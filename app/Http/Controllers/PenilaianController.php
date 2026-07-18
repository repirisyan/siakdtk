<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenilaianRequest;
use App\Models\Absen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\KomponenPenilaian;
use App\Models\Nilai;
use App\Models\RaporAkhir;
use App\Models\Siswa;
use App\Models\SubTema;
use App\Models\Tema;
use App\Models\User;
use Inertia\Inertia;

class PenilaianController extends Controller
{
    public function index()
    {
        $user = $this->currentAssessmentUser();
        $kelasId = request()->query('kelas_id');
        $jadwalId = request()->query('jadwal_id');
        $isSummary = request()->boolean('summary');
        $temaId = request()->query('tema_id');
        $subTemaId = request()->query('sub_tema_id');

        $kelas = $this->kelasForUser($user);
        $jadwals = collect();
        $absens = collect();
        $komponenPenilaians = collect();
        $lockedSiswaIds = collect();
        $temas = collect();
        $subTemas = collect();
        $summaryStudents = collect();

        if ($kelasId) {
            abort_unless($kelas->contains('id', (int) $kelasId), 403);

            $jadwals = $this->getJadwal($user, (int) $kelasId);
            $temas = Tema::query()
                ->whereHas('jadwal', fn ($query) => $query
                    ->where('kelas_id', $kelasId)
                    ->when($user->hasRole('Guru'), fn ($query) => $query->where('guru_id', $user->guru->id)))
                ->orderBy('nama_tema')
                ->get(['id', 'nama_tema']);
        }

        if ($isSummary && $kelasId && $temaId) {
            abort_unless($temas->contains('id', (int) $temaId), 403);

            $subTemas = SubTema::query()
                ->where('tema_id', $temaId)
                ->whereHas('jadwals', fn ($query) => $query
                    ->where('kelas_id', $kelasId)
                    ->when($user->hasRole('Guru'), fn ($query) => $query->where('guru_id', $user->guru->id)))
                ->orderBy('nama_sub_tema')
                ->get(['id', 'tema_id', 'nama_sub_tema']);
        }

        if ($isSummary && $kelasId && $temaId && $subTemaId) {
            abort_unless($subTemas->contains('id', (int) $subTemaId), 403);

            $summaryStudents = Siswa::query()
                ->where('kelas_id', $kelasId)
                ->whereHas('absens.jadwal', fn ($query) => $query
                    ->where('kelas_id', $kelasId)
                    ->where('tema_id', $temaId)
                    ->where('sub_tema_id', $subTemaId)
                    ->when($user->hasRole('Guru'), fn ($query) => $query->where('guru_id', $user->guru->id)))
                ->orderBy('nama')
                ->get(['id', 'nama', 'nis']);
        }

        if ($jadwalId) {
            $jadwal = $this->authorizeJadwal($user, (int) $jadwalId);

            abort_unless($jadwal->kelas_id === (int) $kelasId, 403);

            $absens = $this->getSiswa($user, (int) $kelasId, $jadwal->id);
            $komponenPenilaians = KomponenPenilaian::active()
                ->where('sub_tema_id', $jadwal->sub_tema_id)
                ->orderBy('nama_komponen')
                ->get(['id', 'sub_tema_id', 'nama_komponen', 'deskripsi']);
            $lockedSiswaIds = $this->lockedSiswaIds($jadwal);
        }

        return Inertia::render('Penilaian/Index', [
            'kelas' => $kelas,
            'jadwals' => $jadwals,
            'absens' => $absens,
            'komponenPenilaians' => $komponenPenilaians,
            'lockedSiswaIds' => $lockedSiswaIds,
            'temas' => $temas,
            'subTemas' => $subTemas,
            'summaryStudents' => $summaryStudents,
            'filters' => [
                'kelas_id' => $kelasId,
                'jadwal_id' => $jadwalId,
                'summary' => $isSummary,
                'tema_id' => $temaId,
                'sub_tema_id' => $subTemaId,
            ],
        ]);
    }

    public function summary(Siswa $siswa)
    {
        $user = $this->currentAssessmentUser();
        $kelasId = (int) request()->query('kelas_id');
        $temaId = (int) request()->query('tema_id');
        $subTemaId = (int) request()->query('sub_tema_id');

        abort_unless($kelasId && $temaId && $subTemaId, 404);
        abort_unless($this->kelasForUser($user)->contains('id', $kelasId), 403);
        abort_unless($siswa->kelas_id === $kelasId, 403);

        $kelas = Kelas::findOrFail($kelasId);
        $tema = Tema::findOrFail($temaId);
        $subTema = SubTema::where('tema_id', $temaId)->findOrFail($subTemaId);

        $jadwalIds = Jadwal::query()
            ->where('kelas_id', $kelasId)
            ->where('tema_id', $temaId)
            ->where('sub_tema_id', $subTemaId)
            ->when($user->hasRole('Guru'), fn ($query) => $query->where('guru_id', $user->guru->id))
            ->pluck('id');

        $nilais = Nilai::query()
            ->with('komponenPenilaian:id,nama_komponen')
            ->whereHas('absen', fn ($query) => $query
                ->where('siswa_id', $siswa->id)
                ->whereIn('jadwal_id', $jadwalIds))
            ->orderBy('komponen_penilaian_id')
            ->get(['id', 'absen_id', 'komponen_penilaian_id', 'keterangan']);

        $components = $nilais
            ->groupBy('komponen_penilaian_id')
            ->map(fn ($items) => [
                'id' => $items->first()->komponen_penilaian_id,
                'nama_komponen' => $items->first()->komponenPenilaian?->nama_komponen ?? 'Komponen',
                'keterangan' => $items->pluck('keterangan')->filter()->unique()->values()->join('; ') ?: '-',
            ])->values();

        return Inertia::render('Penilaian/Summary', [
            'siswa' => $siswa->only(['id', 'nama', 'nis']),
            'kelas' => $kelas->only(['id', 'nama_kelas', 'thn_ajaran']),
            'tema' => $tema->only(['id', 'nama_tema']),
            'subTema' => $subTema->only(['id', 'nama_sub_tema']),
            'components' => $components,
            'backUrl' => route('penilaian.index', [
                'kelas_id' => $kelasId,
                'summary' => 1,
                'tema_id' => $temaId,
                'sub_tema_id' => $subTemaId,
            ]),
        ]);
    }

    public function getJadwal(User $user, int $kelasId)
    {
        return Jadwal::with(['guru:id,nama,nip', 'tema:id,nama_tema', 'subTema:id,tema_id,nama_sub_tema'])
            ->where('kelas_id', $kelasId)
            ->when($user->hasRole('Guru'), function ($query) use ($user) {
                $query->where('guru_id', $user->guru->id);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get(['id', 'kelas_id', 'guru_id', 'tema_id', 'sub_tema_id', 'tanggal', 'jam_mulai', 'jam_selesai']);
    }

    public function getSiswa(User $user, int $kelasId, int $jadwalId)
    {
        $jadwal = $this->authorizeJadwal($user, $jadwalId);

        abort_unless($jadwal->kelas_id === $kelasId, 403);

        return Absen::with([
            'siswa:id,nama,nis,kelas_id',
            'nilais:id,absen_id,komponen_penilaian_id,nilai,keterangan',
            'nilais.komponenPenilaian:id,nama_komponen',
        ])
            ->where('jadwal_id', $jadwalId)
            ->orderBy('id')
            ->get();
    }

    public function store(PenilaianRequest $request)
    {
        $data = $request->validated();
        $user = $this->currentAssessmentUser();
        $jadwal = $this->authorizeJadwal($user, (int) $data['jadwal_id']);

        abort_unless($jadwal->kelas_id === (int) $data['kelas_id'], 403);

        $absen = Absen::with('siswa')->findOrFail($data['absen_id']);

        abort_unless(
            $absen->jadwal_id === $jadwal->id
                && $absen->status === 'hadir'
                && $absen->siswa->kelas_id === $jadwal->kelas_id,
            403,
        );
        $this->ensureAcademicDataIsUnlocked($absen->siswa, $jadwal);

        abort_unless(
            KomponenPenilaian::active()
                ->whereKey($data['komponen_penilaian_id'])
                ->where('sub_tema_id', $jadwal->sub_tema_id)
                ->exists(),
            403,
        );

        $nilai = Nilai::updateOrCreate(
            ['absen_id' => $absen->id, 'komponen_penilaian_id' => $data['komponen_penilaian_id']],
            [
                'nilai' => $data['nilai'],
                'keterangan' => $data['keterangan'],
            ],
        );

        $message = $nilai->wasRecentlyCreated
            ? 'Nilai berhasil disimpan.'
            : 'Nilai berhasil diperbarui.';

        return redirect()
            ->route('penilaian.index', [
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal->id,
            ])
            ->with('success', $message);
    }

    private function kelasForUser(User $user)
    {
        return Kelas::query()
            ->active()
            ->when($user->hasRole('Guru'), function ($query) use ($user) {
                $query->whereHas('jadwal', function ($query) use ($user) {
                    $query->where('guru_id', $user->guru->id);
                });
            })
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'thn_ajaran']);
    }

    private function authorizeJadwal(User $user, int $jadwalId): Jadwal
    {
        $jadwal = Jadwal::with(['guru:id,nama,nip', 'kelas:id,nama_kelas,thn_ajaran', 'tema:id,nama_tema', 'subTema:id,tema_id,nama_sub_tema'])
            ->findOrFail($jadwalId);

        if ($user->hasRole('Guru')) {
            abort_unless($jadwal->guru_id === $user->guru->id, 403);
        }

        return $jadwal;
    }

    private function lockedSiswaIds(Jadwal $jadwal)
    {
        return RaporAkhir::query()
            ->where('kelas_id', $jadwal->kelas_id)
            ->where('thn_ajaran', $jadwal->kelas->thn_ajaran)
            ->where('status', 'disetujui')
            ->pluck('siswa_id');
    }

    private function ensureAcademicDataIsUnlocked(Siswa $siswa, Jadwal $jadwal): void
    {
        abort_unless(
            ! $this->lockedSiswaIds($jadwal)->contains($siswa->id),
            403,
            'Nilai tidak dapat diubah karena Rapor Akhir siswa telah disetujui.',
        );
    }

    private function currentAssessmentUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing(['role', 'guru']);

        abort_unless(
            $user->hasRole('Admin')
                || $user->hasRole('Staff Akademik')
                || ($user->hasRole('Guru') && $user->guru),
            403,
        );

        return $user;
    }
}
