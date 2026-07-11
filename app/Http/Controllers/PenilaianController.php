<?php

namespace App\Http\Controllers;

use App\Http\Requests\PenilaianRequest;
use App\Models\Absen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\User;
use Inertia\Inertia;

class PenilaianController extends Controller
{
    public function index()
    {
        $user = $this->currentAssessmentUser();
        $kelasId = request()->query('kelas_id');
        $jadwalId = request()->query('jadwal_id');

        $kelas = $this->kelasForUser($user);
        $jadwals = collect();
        $absens = collect();

        if ($kelasId) {
            abort_unless($kelas->contains('id', (int) $kelasId), 403);

            $jadwals = $this->getJadwal($user, (int) $kelasId);
        }

        if ($jadwalId) {
            $jadwal = $this->authorizeJadwal($user, (int) $jadwalId);

            abort_unless($jadwal->kelas_id === (int) $kelasId, 403);

            $absens = $this->getSiswa($user, (int) $kelasId, $jadwal->id);
        }

        return Inertia::render('Penilaian/Index', [
            'kelas' => $kelas,
            'jadwals' => $jadwals,
            'absens' => $absens,
            'filters' => [
                'kelas_id' => $kelasId,
                'jadwal_id' => $jadwalId,
            ],
        ]);
    }

    public function getJadwal(User $user, int $kelasId)
    {
        return Jadwal::with(['guru:id,nama,nip', 'tema:id,nama_tema'])
            ->where('kelas_id', $kelasId)
            ->when($user->hasRole('Guru'), function ($query) use ($user) {
                $query->where('guru_id', $user->guru->id);
            })
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->get(['id', 'kelas_id', 'guru_id', 'tema_id', 'tanggal', 'jam_mulai', 'jam_selesai']);
    }

    public function getSiswa(User $user, int $kelasId, int $jadwalId)
    {
        $jadwal = $this->authorizeJadwal($user, $jadwalId);

        abort_unless($jadwal->kelas_id === $kelasId, 403);

        return Absen::with([
            'siswa:id,nama,nis,kelas_id',
            'nilai:id,absen_id,nilai,keterangan',
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

        $nilai = Nilai::firstOrCreate(
            ['absen_id' => $absen->id],
            [
                'nilai' => $data['nilai'],
                'keterangan' => $data['keterangan'],
            ],
        );

        $message = $nilai->wasRecentlyCreated
            ? 'Nilai berhasil disimpan.'
            : 'Nilai siswa sudah tercatat.';

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
        $jadwal = Jadwal::with(['guru:id,nama,nip', 'kelas:id,nama_kelas,thn_ajaran', 'tema:id,nama_tema'])
            ->findOrFail($jadwalId);

        if ($user->hasRole('Guru')) {
            abort_unless($jadwal->guru_id === $user->guru->id, 403);
        }

        return $jadwal;
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
