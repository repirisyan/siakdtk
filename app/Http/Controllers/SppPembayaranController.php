<?php

namespace App\Http\Controllers;

use App\Http\Requests\SppPembayaranRequest;
use App\Models\Spp;
use App\Models\SppPembayaran;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SppPembayaranController extends Controller
{
    public function store(SppPembayaranRequest $request, Spp $spp)
    {
        $user = $this->currentSppManager();
        $data = $request->validated();

        $this->ensurePaymentDoesNotExceedBalance($spp, (float) $data['jumlah_bayar']);

        DB::transaction(function () use ($request, $spp, $data, $user) {
            $buktiPembayaran = $request->file('bukti_pembayaran')?->store('spp-payments', 'public');

            $spp->payments()->create([
                ...$data,
                'bukti_pembayaran' => $buktiPembayaran,
                'received_by' => $user->id,
                'status_verifikasi' => 'approved',
                'verified_by' => $user->id,
                'verified_at' => now(),
            ]);
        });

        return redirect()
            ->route('spp.show', $spp)
            ->with('success', 'Pembayaran SPP berhasil dicatat.');
    }

    public function update(SppPembayaranRequest $request, Spp $spp, SppPembayaran $payment)
    {
        $user = $this->currentSppManager();
        $data = $request->validated();

        abort_unless($payment->spp_id === $spp->id, 403);

        $this->ensurePaymentDoesNotExceedBalance($spp, (float) $data['jumlah_bayar'], $payment->id);

        DB::transaction(function () use ($request, $payment, $data, $user) {
            $buktiPembayaran = $payment->bukti_pembayaran;

            if ($request->hasFile('bukti_pembayaran')) {
                if ($buktiPembayaran) {
                    Storage::disk('public')->delete($buktiPembayaran);
                }

                $buktiPembayaran = $request->file('bukti_pembayaran')->store('spp-payments', 'public');
            }

            $payment->update([
                ...$data,
                'bukti_pembayaran' => $buktiPembayaran,
                'received_by' => $user->id,
            ]);
        });

        return redirect()
            ->route('spp.show', $spp)
            ->with('success', 'Pembayaran SPP berhasil diperbarui.');
    }

    public function destroy(Spp $spp, SppPembayaran $payment)
    {
        $this->currentSppManager();

        abort_unless($payment->spp_id === $spp->id, 403);

        DB::transaction(function () use ($payment) {
            if ($payment->bukti_pembayaran) {
                Storage::disk('public')->delete($payment->bukti_pembayaran);
            }

            $payment->delete();
        });

        return redirect()
            ->route('spp.show', $spp)
            ->with('success', 'Pembayaran SPP berhasil dihapus.');
    }

    public function approve(Spp $spp, SppPembayaran $payment)
    {
        $user = $this->currentSppManager();
        abort_unless($payment->spp_id === $spp->id && $payment->status_verifikasi === 'pending', 403);
        $this->ensurePaymentDoesNotExceedBalance($spp, (float) $payment->jumlah_bayar, $payment->id);
        $payment->update(['status_verifikasi' => 'approved', 'verified_by' => $user->id, 'verified_at' => now()]);

        return redirect()->route('spp.show', $spp)->with('success', 'Pembayaran berhasil disetujui.');
    }

    public function reject(Spp $spp, SppPembayaran $payment)
    {
        $user = $this->currentSppManager();
        abort_unless($payment->spp_id === $spp->id && $payment->status_verifikasi === 'pending', 403);
        $payment->update(['status_verifikasi' => 'rejected', 'verified_by' => $user->id, 'verified_at' => now()]);

        return redirect()->route('spp.show', $spp)->with('success', 'Pembayaran berhasil ditolak.');
    }

    private function ensurePaymentDoesNotExceedBalance(Spp $spp, float $jumlahBayar, ?int $paymentId = null): void
    {
        $totalDibayar = (float) $spp->approvedPayments()
            ->when($paymentId, fn ($query) => $query->whereKeyNot($paymentId))
            ->sum('jumlah_bayar');

        if ($totalDibayar + $jumlahBayar > (float) $spp->nominal) {
            throw ValidationException::withMessages([
                'jumlah_bayar' => 'Jumlah pembayaran melebihi sisa tagihan SPP.',
            ]);
        }
    }

    private function currentSppManager(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing('role');

        abort_unless($user->hasRole('Admin') || $user->hasRole('Staff Administrasi'), 403);

        return $user;
    }
}
