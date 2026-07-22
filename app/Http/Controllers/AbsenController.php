<?php

namespace App\Http\Controllers;

use App\Http\Requests\AbsenRequest;
use App\Models\Absen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\RaporAkhir;
use App\Models\Siswa;
use App\Models\User;
use Inertia\Inertia;

class AbsenController extends Controller
{
    public function index()
    {
        $user = $this->currentAttendanceUser();
        $kelasId = request()->query('kelas_id');
        $jadwalId = request()->query('jadwal_id');

        $kelas = $this->kelasForUser($user);
        $jadwals = collect();
        $siswas = collect();
        $lockedSiswaIds = collect();

        if ($kelasId) {
            abort_unless($kelas->contains('id', (int) $kelasId), 403);

            $jadwals = $this->jadwalsForUser($user, (int) $kelasId);
        }

        if ($jadwalId) {
            $jadwal = $this->authorizeJadwal($user, (int) $jadwalId);

            abort_unless($jadwal->kelas_id === (int) $kelasId, 403);

            $siswas = Siswa::with([
                'absens' => function ($query) use ($jadwalId) {
                    $query->where('jadwal_id', $jadwalId)->withCount('nilais');
                },
            ])
                ->where(['kelas_id' => $jadwal->kelas_id, 'status' => 'aktif'])
                ->orderBy('nama')
                ->get();
            $lockedSiswaIds = $this->lockedSiswaIds($jadwal);
        }

        return Inertia::render('Absensi/Index', [
            'kelas' => $kelas,
            'jadwals' => $jadwals,
            'siswas' => $siswas,
            'lockedSiswaIds' => $lockedSiswaIds,
            'filters' => [
                'kelas_id' => $kelasId,
                'jadwal_id' => $jadwalId,
            ],
        ]);
    }

    public function store(AbsenRequest $request)
    {
        $data = $request->validated();
        $user = $this->currentAttendanceUser();
        $jadwal = $this->authorizeJadwal($user, (int) $data['jadwal_id']);

        abort_unless($jadwal->kelas_id === (int) $data['kelas_id'], 403);

        $siswa = Siswa::findOrFail($data['siswa_id']);

        abort_unless($siswa->kelas_id === $jadwal->kelas_id, 403);
        $this->ensureAcademicDataIsUnlocked($siswa, $jadwal);

        $absen = Absen::firstOrCreate(
            [
                'siswa_id' => $siswa->id,
                'jadwal_id' => $jadwal->id,
            ],
            [
                'status' => $data['status'],
                'keterangan' => $data['keterangan'],
            ],
        );

        $message = $absen->wasRecentlyCreated
            ? 'Absensi berhasil dicatat.'
            : 'Absensi siswa sudah tercatat.';

        return redirect()
            ->route('absensi.index', [
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal->id,
            ])
            ->with('success', $message);
    }

    public function destroy(Absen $absen)
    {
        $user = $this->currentAttendanceUser();
        $jadwal = $this->authorizeJadwal($user, $absen->jadwal_id);

        abort_unless(
            $absen->siswa()->value('kelas_id') === $jadwal->kelas_id,
            403,
        );
        $this->ensureAcademicDataIsUnlocked($absen->siswa()->firstOrFail(), $jadwal);

        if ($absen->nilais()->exists()) {
            return redirect()->route('absensi.index', [
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal->id,
            ])->with('error', 'Absensi sudah memiliki nilai dan tidak dapat dihapus.');
        }

        $absen->delete();

        return redirect()
            ->route('absensi.index', [
                'kelas_id' => $jadwal->kelas_id,
                'jadwal_id' => $jadwal->id,
            ])
            ->with('success', 'Absensi berhasil dibatalkan.');
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

    private function jadwalsForUser(User $user, int $kelasId)
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

    private function authorizeJadwal(User $user, int $jadwalId): Jadwal
    {
        $jadwal = Jadwal::with(['guru:id,nama,nip', 'kelas:id,nama_kelas,thn_ajaran', 'tema:id,nama_tema'])
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
            'Absensi tidak dapat diubah karena Rapor Akhir siswa telah disetujui.',
        );
    }

    private function currentAttendanceUser(): User
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
