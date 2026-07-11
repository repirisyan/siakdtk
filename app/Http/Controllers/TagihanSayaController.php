<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParentSppPaymentRequest;
use App\Models\MidtransTransaction;
use App\Models\Spp;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

class TagihanSayaController extends Controller
{
    public function index()
    {
        $user = $this->parentUser();
        $siswas = $user->siswas()->with('kelas:id,nama_kelas,thn_ajaran')->orderBy('nama')->get();
        $siswaId = request()->query('siswa_id');
        abort_unless(! $siswaId || $siswas->contains('id', (int) $siswaId), 403);
        $spps = Spp::withSum('approvedPayments as total_dibayar', 'jumlah_bayar')
            ->with('siswa:id,nama,nis')
            ->whereIn('siswa_id', $siswas->pluck('id'))
            ->when($siswaId, fn ($query) => $query->where('siswa_id', $siswaId))
            ->latest('tanggal_tagihan')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('TagihanSaya/Index', ['spps' => $spps, 'siswas' => $siswas, 'filters' => ['siswa_id' => $siswaId]]);
    }

    public function show(Spp $spp)
    {
        $user = $this->parentUser();
        $this->authorizeSpp($user, $spp);

        return Inertia::render('TagihanSaya/Show', [
            'spp' => $spp->load(['payments' => fn ($query) => $query->latest('tanggal_bayar'), 'payments.verifier:id,name'])
                ->loadSum('approvedPayments as total_dibayar', 'jumlah_bayar'),
            'midtransClientKey' => config('services.midtrans.client_key'),
        ]);
    }

    public function storeManualPayment(ParentSppPaymentRequest $request, Spp $spp)
    {
        $user = $this->parentUser();
        $this->authorizeSpp($user, $spp);
        $data = $request->validated();
        $this->ensureBalance($spp, (float) $data['jumlah_bayar']);

        DB::transaction(function () use ($request, $spp, $data, $user) {
            $spp->payments()->create([
                ...$data,
                'metode_pembayaran' => 'manual',
                'status_verifikasi' => 'pending',
                'bukti_pembayaran' => $request->file('bukti_pembayaran')->store('spp-payments', 'public'),
                'received_by' => $user->id,
            ]);
        });

        return redirect()->route('tagihan-saya.show', $spp)->with('success', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi.');
    }

    public function createMidtransTransaction(Spp $spp)
    {
        $user = $this->parentUser();
        $this->authorizeSpp($user, $spp);
        $spp->loadSum('approvedPayments as total_dibayar', 'jumlah_bayar');
        $sisa = (float) $spp->nominal - (float) ($spp->total_dibayar ?? 0);

        if ($sisa <= 0) {
            throw ValidationException::withMessages(['spp' => 'Tagihan ini sudah lunas.']);
        }

        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');

        $existingTransaction = MidtransTransaction::query()->where('spp_id', $spp->id)->where('user_id', $user->id)->where('transaction_status', 'pending')->latest()->first();

        if ($existingTransaction) {

            return response()->json([

                'snap_token' => $existingTransaction->snap_token,

                'client_key' => config('services.midtrans.client_key'),

            ]);

        }

        $orderId = 'SPP-'.$spp->id.'-'.Str::upper(Str::random(12));
        $snapToken = Snap::getSnapToken([
            'transaction_details' => ['order_id' => $orderId, 'gross_amount' => (int) $sisa],
            'customer_details' => ['first_name' => $user->name, 'email' => $user->email],
            'callbacks' => [
                'finish' => route('tagihan-saya.show', $spp),
            ],
        ]);

        MidtransTransaction::create(['spp_id' => $spp->id, 'user_id' => $user->id, 'order_id' => $orderId, 'snap_token' => $snapToken, 'gross_amount' => $sisa, 'transaction_status' => 'pending']);

        return response()->json(['snap_token' => $snapToken, 'client_key' => config('services.midtrans.client_key')]);
    }

    private function parentUser(): User
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        $user->loadMissing(['role', 'siswas']);
        abort_unless($user->hasRole('Orangtua Siswa') && $user->siswas->isNotEmpty(), 403);

        return $user;
    }

    private function authorizeSpp(User $user, Spp $spp): void
    {
        abort_unless($user->siswas()->whereKey($spp->siswa_id)->exists(), 403);
    }

    private function ensureBalance(Spp $spp, float $amount): void
    {
        $paid = (float) $spp->approvedPayments()->sum('jumlah_bayar');
        if ($paid + $amount > (float) $spp->nominal) {
            throw ValidationException::withMessages(['jumlah_bayar' => 'Jumlah pembayaran melebihi sisa tagihan.']);
        }
    }
}
