<?php

namespace App\Http\Controllers;

use App\Http\Requests\RaporAkhirDetailRequest;
use App\Models\Absen;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\RaporAkhir;
use App\Models\RaporAkhirDetail;
use App\Models\SchoolSetting;
use App\Models\Siswa;
use App\Models\Tema;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RaporAkhirController extends Controller
{
    public function index()
    {
        $user = $this->currentUser();
        $kelasId = request()->integer('kelas_id') ?: null;
        $tahunAjaran = request()->query('thn_ajaran');
        $siswaId = request()->integer('siswa_id') ?: null;
        $kelas = $this->kelasForUser($user);
        $selectedKelas = null;
        $siswas = collect();
        $temas = collect();
        $raporAkhirs = collect();
        $references = collect();

        if ($kelasId) {
            abort_unless($kelas->contains('id', $kelasId), 403);
            $selectedKelas = Kelas::findOrFail($kelasId);
            abort_unless(! $tahunAjaran || (string) $selectedKelas->thn_ajaran === (string) $tahunAjaran, 403);
            $siswas = Siswa::where('kelas_id', $kelasId)->where('status', 'aktif')
                ->when($siswaId, fn ($query) => $query->whereKey($siswaId))
                ->orderBy('nama')->get(['id', 'nama', 'nis', 'user_id', 'kelas_id']);
            $temas = Tema::active()->where('thn_ajaran', $selectedKelas->thn_ajaran)
                ->whereHas('jadwal', fn ($query) => $query->where('kelas_id', $kelasId))
                ->orderBy('nama_tema')->get(['id', 'nama_tema', 'thn_ajaran']);
            $raporAkhirs = RaporAkhir::with(['details.tema:id,nama_tema', 'details.guru:id,nama,nip'])
                ->where('kelas_id', $kelasId)->where('thn_ajaran', $selectedKelas->thn_ajaran)
                ->whereIn('siswa_id', $siswas->pluck('id'))->get();
            $references = Nilai::with([
                'absen.siswa:id,nama',
                'absen.jadwal:id,tema_id,sub_tema_id',
                'absen.jadwal.subTema:id,nama_sub_tema',
                'komponenPenilaian:id,nama_komponen',
            ])
                ->whereHas('absen', fn ($query) => $query->whereIn('siswa_id', $siswas->pluck('id')))
                ->whereHas('absen.jadwal', fn ($query) => $query->where('kelas_id', $kelasId)->whereIn('tema_id', $temas->pluck('id')))
                ->get(['id', 'absen_id', 'komponen_penilaian_id', 'nilai', 'keterangan']);
        }

        return Inertia::render('RaporAkhir/Index', [
            'kelas' => $kelas,
            'tahunAjaranOptions' => Kelas::select('thn_ajaran')->distinct()->orderByDesc('thn_ajaran')->pluck('thn_ajaran'),
            'selectedKelas' => $selectedKelas,
            'siswas' => $siswas,
            'temas' => $temas,
            'raporAkhirs' => $raporAkhirs,
            'assessmentReferences' => $references,
            'canManage' => $user->hasRole('Guru'),
            'canApprove' => $user->hasRole('Kepsek'),
            'filters' => compact('kelasId', 'tahunAjaran', 'siswaId'),
        ]);
    }

    public function store(RaporAkhirDetailRequest $request)
    {
        $user = $this->currentGuru();
        $data = $request->validated();
        $kelas = $this->authorizeKelas($user, (int) $data['kelas_id']);
        abort_unless((string) $kelas->thn_ajaran === (string) $data['thn_ajaran'], 403);
        abort_unless(Siswa::whereKey($data['siswa_id'])->where('kelas_id', $kelas->id)->exists(), 403);
        abort_unless(Tema::active()->whereKey($data['tema_id'])->where('thn_ajaran', $kelas->thn_ajaran)->exists(), 403);

        DB::transaction(function () use ($data, $kelas, $user) {
            $rapor = RaporAkhir::firstOrCreate([
                'siswa_id' => $data['siswa_id'], 'kelas_id' => $kelas->id, 'thn_ajaran' => $kelas->thn_ajaran,
            ]);
            abort_unless(in_array($rapor->status, ['draft', 'ditolak'], true), 403);
            RaporAkhirDetail::updateOrCreate(
                ['rapor_akhir_id' => $rapor->id, 'tema_id' => $data['tema_id']],
                ['guru_id' => $user->guru->id, 'keterangan' => $data['keterangan']],
            );
            $rapor->update(['status' => 'draft', 'approved_by' => null, 'approved_at' => null, 'rejected_by' => null, 'rejected_at' => null, 'catatan_penolakan' => null]);
        });

        return redirect()->route('rapor-akhir.index', ['kelas_id' => $kelas->id, 'thn_ajaran' => $kelas->thn_ajaran])
            ->with('success', 'Catatan Rapor Akhir berhasil disimpan.');
    }

    public function submit(RaporAkhir $raporAkhir)
    {
        $user = $this->currentGuru();
        $this->authorizeKelas($user, $raporAkhir->kelas_id);
        abort_unless(in_array($raporAkhir->status, ['draft', 'ditolak'], true), 403);
        $temaIds = $this->temaIds($raporAkhir->kelas_id, $raporAkhir->thn_ajaran);
        abort_unless($temaIds->isNotEmpty() && $raporAkhir->details()->whereIn('tema_id', $temaIds)->count() === $temaIds->count(), 422, 'Semua tema harus memiliki catatan sebelum diajukan.');
        $raporAkhir->update(['status' => 'menunggu_validasi', 'catatan_penolakan' => null, 'rejected_by' => null, 'rejected_at' => null]);

        return back()->with('success', 'Rapor Akhir berhasil diajukan untuk validasi.');
    }

    public function approve(RaporAkhir $raporAkhir)
    {
        $user = $this->currentKepsek();
        abort_unless($raporAkhir->status === 'menunggu_validasi', 422);
        $raporAkhir->update(['status' => 'disetujui', 'approved_by' => $user->id, 'approved_at' => now(), 'rejected_by' => null, 'rejected_at' => null, 'catatan_penolakan' => null]);

        return back()->with('success', 'Rapor Akhir berhasil disetujui.');
    }

    public function reject(RaporAkhir $raporAkhir)
    {
        $user = $this->currentKepsek();
        $data = request()->validate(['catatan_penolakan' => ['required', 'string', 'max:5000']]);
        abort_unless($raporAkhir->status === 'menunggu_validasi', 422);
        $raporAkhir->update(['status' => 'ditolak', 'rejected_by' => $user->id, 'rejected_at' => now(), 'catatan_penolakan' => $data['catatan_penolakan'], 'approved_by' => null, 'approved_at' => null]);

        return back()->with('success', 'Rapor Akhir dikembalikan untuk diperbaiki.');
    }

    public function approveAll()
    {
        $user = $this->currentKepsek();
        $data = request()->validate(['kelas_id' => ['required', 'exists:kelas,id'], 'thn_ajaran' => ['required', 'digits:4']]);
        $kelas = Kelas::findOrFail($data['kelas_id']);
        abort_unless((string) $kelas->thn_ajaran === (string) $data['thn_ajaran'], 403);
        $count = RaporAkhir::where('kelas_id', $kelas->id)->where('thn_ajaran', $kelas->thn_ajaran)->where('status', 'menunggu_validasi')->update(['status' => 'disetujui', 'approved_by' => $user->id, 'approved_at' => now()]);

        return back()->with($count ? 'success' : 'warning', $count ? "{$count} Rapor Akhir berhasil disetujui." : 'Tidak ada Rapor Akhir yang menunggu validasi.');
    }

    public function printClass(Kelas $kelas)
    {
        $this->authorizeKelas($this->currentUser(), $kelas->id);

        $rapors = RaporAkhir::with($this->printRelations())
            ->where('kelas_id', $kelas->id)
            ->where('thn_ajaran', $kelas->thn_ajaran)
            ->where('status', 'disetujui')
            ->orderBy('siswa_id')
            ->get();

        abort_unless($rapors->isNotEmpty(), 404, 'Belum ada Rapor Akhir yang disetujui untuk kelas ini.');

        return view('rapor-akhir.print', [
            'school' => SchoolSetting::current(),
            'kelas' => $kelas,
            'rapors' => $rapors,
            'attendance' => $this->attendanceBySiswa($rapors, $kelas),
        ]);
    }

    public function printStudent(RaporAkhir $raporAkhir)
    {
        $this->authorizeKelas($this->currentUser(), $raporAkhir->kelas_id);
        abort_unless($raporAkhir->status === 'disetujui', 403);
        $raporAkhir->load($this->printRelations());

        return view('rapor-akhir.print', [
            'school' => SchoolSetting::current(),
            'kelas' => $raporAkhir->kelas,
            'rapors' => collect([$raporAkhir]),
            'attendance' => $this->attendanceBySiswa(collect([$raporAkhir]), $raporAkhir->kelas),
        ]);
    }

    private function printRelations(): array
    {
        return [
            'siswa:id,kelas_id,nama,nis,nisn,tmp_lahir,tgl_lahir,jk,agama,anak_ke,nama_ayah,nama_ibu,nohp_ayah,nohp_ibu,pekerjaan,pekerjaan_ibu,alamat,desa_wali,kecamatan_wali,kabupaten_wali,provinsi_wali,tinggi_bdn,berat_bdn',
            'kelas:id,nama_kelas,thn_ajaran,semester',
            'details.tema:id,nama_tema',
            'details.guru:id,nama,nip',
            'approver:id,name',
            'approver.guru:id,user_id,nip',
        ];
    }

    private function attendanceBySiswa($rapors, Kelas $kelas)
    {
        return Absen::query()
            ->selectRaw("siswa_id, SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir, SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin, SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit, SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) as alfa")
            ->whereIn('siswa_id', $rapors->pluck('siswa_id'))
            ->whereHas('jadwal', fn ($query) => $query->where('kelas_id', $kelas->id))
            ->groupBy('siswa_id')
            ->get()
            ->keyBy('siswa_id');
    }

    private function temaIds(int $kelasId, string|int $tahunAjaran)
    {
        return Tema::active()->where('thn_ajaran', $tahunAjaran)->whereHas('jadwal', fn ($query) => $query->where('kelas_id', $kelasId))->pluck('id');
    }

    private function kelasForUser(User $user)
    {
        return Kelas::active()->when($user->hasRole('Guru'), fn ($query) => $query->whereHas('jadwal', fn ($jadwal) => $jadwal->where('guru_id', $user->guru->id)))->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'thn_ajaran']);
    }

    private function authorizeKelas(User $user, int $kelasId): Kelas
    {
        $kelas = Kelas::findOrFail($kelasId);
        if ($user->hasRole('Guru')) {
            abort_unless($kelas->jadwal()->where('guru_id', $user->guru->id)->exists(), 403);
        }

        return $kelas;
    }

    private function currentUser(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing(['role', 'guru']);
        abort_unless($user->hasRole('Admin') || $user->hasRole('Staff Akademik') || $user->hasRole('Kepsek') || ($user->hasRole('Guru') && $user->guru), 403);

        return $user;
    }

    private function currentGuru(): User
    {
        $user = $this->currentUser();
        abort_unless($user->hasRole('Guru') && $user->guru, 403);

        return $user;
    }

    private function currentKepsek(): User
    {
        $user = $this->currentUser();
        abort_unless($user->hasRole('Kepsek'), 403);

        return $user;
    }
}
