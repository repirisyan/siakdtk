<?php

namespace App\Http\Controllers;

use App\Models\Rapor;
use App\Models\User;
use Inertia\Inertia;

class RaporAnakController extends Controller
{
    public function index()
    {
        $user = $this->currentParentUser();
        $siswas = $user->siswas()->with('kelas:id,nama_kelas,thn_ajaran')->orderBy('nama')->get();
        $tahunAjaran = request()->query('thn_ajaran');
        $siswaId = request()->query('siswa_id');

        abort_unless(! $siswaId || $siswas->contains('id', (int) $siswaId), 403);

        $tahunAjaranOptions = Rapor::whereIn('siswa_id', $siswas->pluck('id'))
            ->where('status', 'disetujui')
            ->select('thn_ajaran')->distinct()->orderByDesc('thn_ajaran')->pluck('thn_ajaran');
        $rapors = Rapor::with(['tema:id,nama_tema', 'guru:id,nama,nip', 'validator:id,name,email', 'siswa:id,kelas_id,nama,nis'])
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->where('status', 'disetujui')
            ->when($siswaId, fn ($query) => $query->where('siswa_id', $siswaId))
            ->when($tahunAjaran, fn ($query) => $query->where('thn_ajaran', $tahunAjaran))
            ->orderByDesc('thn_ajaran')->orderBy('tema_id')->get();

        return Inertia::render('RaporAnak/Index', [
            'siswas' => $siswas,
            'rapors' => $rapors,
            'tahunAjaranOptions' => $tahunAjaranOptions,
            'filters' => ['thn_ajaran' => $tahunAjaran, 'siswa_id' => $siswaId],
        ]);
    }

    public function show(Rapor $rapor)
    {
        $user = $this->currentParentUser();
        abort_unless($rapor->status === 'disetujui' && $user->siswas()->whereKey($rapor->siswa_id)->exists(), 403);

        return Inertia::render('RaporAnak/Show', [
            'rapor' => $rapor->load(['tema:id,nama_tema', 'guru:id,nama,nip', 'validator:id,name,email']),
            'siswa' => $rapor->siswa()->with('kelas:id,nama_kelas,thn_ajaran')->firstOrFail(),
        ]);
    }

    private function currentParentUser(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing(['role', 'siswas']);
        abort_unless($user->hasRole('Orangtua Siswa') && $user->siswas->isNotEmpty(), 403);

        return $user;
    }
}
