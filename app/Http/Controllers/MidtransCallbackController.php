<?php

namespace App\Http\Controllers;

use App\Models\MidtransTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MidtransCallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->all();
        $signature = hash('sha512', ($data['order_id'] ?? '').($data['status_code'] ?? '').($data['gross_amount'] ?? '').config('services.midtrans.server_key'));
        abort_unless(hash_equals($signature, $data['signature_key'] ?? ''), 403);

        $transaction = MidtransTransaction::where('order_id', $data['order_id'])->firstOrFail();

        DB::transaction(function () use ($transaction, $data) {
            $status = $data['transaction_status'] ?? 'pending';
            $transaction->update(['transaction_id' => $data['transaction_id'] ?? null, 'payment_type' => $data['payment_type'] ?? null, 'transaction_status' => $status, 'response_payload' => $data, 'paid_at' => in_array($status, ['settlement', 'capture'], true) ? now() : null]);

            if (in_array($status, ['settlement', 'capture'], true)) {
                $transaction->spp->payments()->firstOrCreate(
                    ['metode_pembayaran' => 'midtrans', 'keterangan' => 'Midtrans order: '.$transaction->order_id],
                    ['tanggal_bayar' => now()->toDateString(), 'jumlah_bayar' => $transaction->gross_amount, 'status_verifikasi' => 'approved', 'verified_at' => now(), 'received_by' => $transaction->user_id],
                );
            }
        });

        return response()->json(['status' => 'ok']);
    }
}
