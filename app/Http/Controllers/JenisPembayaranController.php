<?php

namespace App\Http\Controllers;

use App\Http\Requests\JenisPembayaranRequest;
use App\Models\JenisPembayaran;
use App\Models\User;
use Inertia\Inertia;

class JenisPembayaranController extends Controller
{
    public function index()
    {
        $this->currentManager();
        $search = request()->query('search');

        return Inertia::render('JenisPembayaran/Index', [
            'jenisPembayarans' => JenisPembayaran::query()
                ->when($search, fn ($query) => $query->where('nama_jenis', 'like', "%{$search}%"))
                ->orderBy('nama_jenis')
                ->paginate(10)
                ->withQueryString(),
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        $this->currentManager();

        return Inertia::render('JenisPembayaran/Create');
    }

    public function store(JenisPembayaranRequest $request)
    {
        JenisPembayaran::create([...$request->validated(), 'status' => $request->boolean('status', true)]);

        return redirect()->route('jenis-pembayaran.index')->with('success', 'Jenis pembayaran berhasil dibuat.');
    }

    public function edit(JenisPembayaran $jenisPembayaran)
    {
        $this->currentManager();

        return Inertia::render('JenisPembayaran/Edit', ['jenisPembayaran' => $jenisPembayaran]);
    }

    public function update(JenisPembayaranRequest $request, JenisPembayaran $jenisPembayaran)
    {
        $jenisPembayaran->update([...$request->validated(), 'status' => $request->boolean('status')]);

        return redirect()->route('jenis-pembayaran.index')->with('success', 'Jenis pembayaran berhasil diperbarui.');
    }

    public function destroy(JenisPembayaran $jenisPembayaran)
    {
        $this->currentManager();

        if ($jenisPembayaran->spps()->exists()) {
            return back()->with('error', 'Jenis pembayaran sudah digunakan. Nonaktifkan melalui form edit untuk menjaga data tagihan.');
        }

        $jenisPembayaran->delete();

        return redirect()->route('jenis-pembayaran.index')->with('success', 'Jenis pembayaran berhasil dihapus.');
    }

    private function currentManager(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing('role');
        abort_unless($user->hasRole('Admin') || $user->hasRole('Staff Administrasi'), 403);

        return $user;
    }
}
