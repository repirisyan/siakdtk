<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateSppRequest;
use App\Http\Requests\SendSppNotificationByFilterRequest;
use App\Http\Requests\SendSppNotificationRequest;
use App\Http\Requests\SppRequest;
use App\Jobs\SendSppBillingNotification;
use App\Models\JenisPembayaran;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Spp;
use App\Models\SppNotificationLog;
use App\Models\SppPembayaran;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SppController extends Controller
{
    public function index()
    {
        $user = $this->currentSppUser();

        $search = request()->query('search');
        $sort = request()->query('sort', 'id');
        $direction = request()->query('direction', 'desc');
        $kelasId = request()->query('kelas_id');
        $thnAjaran = request()->query('thn_ajaran');
        $status = request()->query('status');
        $jenisPembayaran = request()->query('jenis_pembayaran');
        $sppSorts = ['id', 'thn_ajaran', 'nominal', 'created_at'];

        $query = Spp::with([
            'siswa:id,kelas_id,nama,nis',
            'siswa.kelas:id,nama_kelas,thn_ajaran',
            'notifier:id,name',
        ]);
        $this->applyFilters($query, compact('search', 'kelasId', 'thnAjaran', 'status', 'jenisPembayaran'));

        $summary = (clone $query)->selectRaw('COALESCE(SUM(nominal), 0) as total_tagihan')->first();
        $totalDibayar = (float) SppPembayaran::whereIn('pembayaran_id', (clone $query)->select('id'))->where('status_verifikasi', 'approved')->sum('jumlah_bayar');

        $spps = $query
            ->withSum('approvedPayments as total_dibayar', 'jumlah_bayar')
            ->when(in_array($sort, $sppSorts), function ($query) use ($sort, $direction) {
                $query->orderBy($sort, $direction);
            }, function ($query) use ($direction) {
                $query->orderBy('id', $direction);
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Spp/Index', [
            'spps' => $spps,
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'kelas_id' => $kelasId,
                'thn_ajaran' => $thnAjaran,
                'status' => $status,
                'jenis_pembayaran' => $jenisPembayaran,
            ],
            'kelasOptions' => Kelas::active()->orderBy('nama_kelas')->get(['id', 'nama_kelas', 'thn_ajaran']),
            'tahunAjaranOptions' => Kelas::active()->select('thn_ajaran')->distinct()->orderByDesc('thn_ajaran')->pluck('thn_ajaran'),
            'jenisPembayaranOptions' => JenisPembayaran::active()->orderBy('nama_jenis')->get(['id', 'nama_jenis']),
            'summary' => [
                'total_tagihan' => (float) ($summary->total_tagihan ?? 0),
                'total_dibayar' => $totalDibayar,
                'total_sisa_tagihan' => max((float) ($summary->total_tagihan ?? 0) - $totalDibayar, 0),
            ],
            'canManageSpp' => $this->canManageSpp($user),
        ]);
    }

    public function create()
    {
        $this->currentSppManager();

        return Inertia::render('Spp/Create', [
            'siswas' => $this->siswaOptions(),
            'jenisPembayarans' => $this->jenisPembayaranOptions(),
        ]);
    }

    public function store(SppRequest $request)
    {
        $this->currentSppManager();

        Spp::create($this->sppData($request->validated()));

        return redirect()
            ->route('spp.index')
            ->with('success', 'Tagihan SPP berhasil dibuat.');
    }

    public function show(Spp $spp)
    {
        $user = $this->currentSppUser();

        return Inertia::render('Spp/Show', [
            'spp' => $this->loadSpp($spp),
            'canManageSpp' => $this->canManageSpp($user),
        ]);
    }

    public function edit(Spp $spp)
    {
        $this->currentSppManager();

        return Inertia::render('Spp/Edit', [
            'spp' => $spp->load('siswa:id,nama,nis'),
            'siswas' => $this->siswaOptions(),
            'jenisPembayarans' => $this->jenisPembayaranOptions(),
        ]);
    }

    public function update(SppRequest $request, Spp $spp)
    {
        $this->currentSppManager();
        $data = $this->sppData($request->validated());

        if ((float) $data['nominal'] < (float) $spp->approvedPayments()->sum('jumlah_bayar')) {
            throw ValidationException::withMessages([
                'nominal' => 'Nominal tagihan tidak boleh lebih kecil dari total pembayaran yang sudah tercatat.',
            ]);
        }

        $spp->update($data);

        return redirect()
            ->route('spp.index')
            ->with('success', 'Tagihan SPP berhasil diperbarui.');
    }

    public function destroy(Spp $spp)
    {
        $this->currentSppManager();

        DB::transaction(function () use ($spp) {
            $spp->load('payments');

            foreach ($spp->payments as $payment) {
                if ($payment->bukti_pembayaran) {
                    Storage::disk('public')->delete($payment->bukti_pembayaran);
                }
            }

            $spp->delete();
        });

        return redirect()
            ->route('spp.index')
            ->with('success', 'Tagihan SPP berhasil dihapus.');
    }

    public function generate(GenerateSppRequest $request)
    {
        $data = $request->validated();
        $this->currentSppManager();
        $jenisPembayaran = JenisPembayaran::active()->findOrFail($data['jenis_pembayaran_id']);

        $siswas = Siswa::query()
            ->where('status', 'aktif')
            ->when($data['target'] === 'kelas', fn ($query) => $query->where('kelas_id', $data['kelas_id']))
            ->when($data['target'] === 'tahun_ajaran', function ($query) use ($data) {
                $query->whereHas('kelas', fn ($query) => $query->where('thn_ajaran', $data['thn_ajaran']));
            })
            ->with('kelas:id,thn_ajaran')
            ->get(['id', 'kelas_id']);

        [$created, $skipped] = DB::transaction(function () use ($data, $siswas, $jenisPembayaran) {
            $created = 0;
            $skipped = 0;

            foreach ($siswas as $siswa) {
                $exists = Spp::where([
                    'siswa_id' => $siswa->id,
                    'jenis_pembayaran' => $jenisPembayaran->nama_jenis,
                    'tanggal_tagihan' => $data['tanggal_tagihan'],
                ])->exists();

                if ($exists) {
                    $skipped++;

                    continue;
                }

                Spp::create([
                    'siswa_id' => $siswa->id,
                    'thn_ajaran' => $siswa->kelas->thn_ajaran,
                    'jenis_pembayaran_id' => $jenisPembayaran->id,
                    'jenis_pembayaran' => $jenisPembayaran->nama_jenis,
                    'nominal' => $data['nominal'],
                    'tanggal_tagihan' => $data['tanggal_tagihan'],
                    'jatuh_tempo' => $data['jatuh_tempo'],
                    'keterangan' => $data['keterangan'],
                ]);
                $created++;
            }

            return [$created, $skipped];
        });

        $message = "Berhasil membuat {$created} tagihan.";

        if ($skipped) {
            $message .= " {$skipped} dilewati karena sudah ada.";
        }

        return redirect()->route('spp.index')->with('success', $message);
    }

    public function sendNotification(Spp $spp)
    {
        $sender = $this->currentSppManager();

        $this->queueNotifications(collect([$spp->id]), $sender, 'single');

        return redirect()->route('spp.index')->with('success', 'Notifikasi pembayaran berhasil dimasukkan ke antrean pengiriman.');
    }

    public function sendNotifications(SendSppNotificationRequest $request)
    {
        $sender = $this->currentSppManager();
        $ids = collect($request->validated('spp_ids'))->unique()->values();

        $count = $this->queueNotifications($ids, $sender, 'selected');

        return redirect()->route('spp.index')->with('success', "{$count} notifikasi berhasil dimasukkan ke antrean pengiriman.");
    }

    public function sendNotificationsByFilter(SendSppNotificationByFilterRequest $request)
    {
        $sender = $this->currentSppManager();
        $filters = $request->validated();
        $query = Spp::query();
        $this->applyFilters($query, [
            'search' => $filters['search'] ?? null,
            'kelasId' => $filters['kelas_id'] ?? null,
            'thnAjaran' => $filters['thn_ajaran'] ?? null,
            'status' => $filters['status'] ?? null,
            'jenisPembayaran' => $filters['jenis_pembayaran'] ?? null,
        ]);
        $ids = $query->pluck('id');
        $count = $this->queueNotifications($ids, $sender, 'filter', $filters);

        return redirect()->route('spp.index', $filters)->with('success', "{$count} notifikasi berhasil dimasukkan ke antrean pengiriman.");
    }

    private function loadSpp(Spp $spp): Spp
    {
        return $spp->load([
            'siswa:id,kelas_id,nama,nis',
            'siswa.kelas:id,nama_kelas,thn_ajaran',
            'payments' => fn ($query) => $query->latest('tanggal_bayar'),
            'payments.receiver:id,name,email',
        ])->loadSum('approvedPayments as total_dibayar', 'jumlah_bayar');
    }

    private function siswaOptions()
    {
        return Siswa::with('kelas:id,nama_kelas,thn_ajaran')
            ->where('status', 'aktif')
            ->whereHas('kelas', fn ($query) => $query->where('status', true))
            ->orderBy('nama')
            ->get(['id', 'kelas_id', 'nama', 'nis']);
    }

    private function jenisPembayaranOptions()
    {
        return JenisPembayaran::active()->orderBy('nama_jenis')->get(['id', 'nama_jenis']);
    }

    private function sppData(array $data): array
    {
        $jenisPembayaran = JenisPembayaran::active()->findOrFail($data['jenis_pembayaran_id']);

        return [...$data, 'jenis_pembayaran' => $jenisPembayaran->nama_jenis];
    }

    private function applyFilters($query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('siswa', function ($query) use ($search) {
                    $query->where('nama', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%");
                });
            })
            ->when($filters['kelasId'] ?? null, fn ($query, $kelasId) => $query->whereHas('siswa', fn ($query) => $query->where('kelas_id', $kelasId)))
            ->when($filters['thnAjaran'] ?? null, fn ($query, $thnAjaran) => $query->where('thn_ajaran', $thnAjaran))
            ->when($filters['jenisPembayaran'] ?? null, fn ($query, $jenisPembayaran) => $query->where('jenis_pembayaran', $jenisPembayaran))
            ->when(($filters['status'] ?? null) === 'lunas', fn ($query) => $query->whereRaw("(SELECT COALESCE(SUM(jumlah_bayar), 0) FROM detail_pembayarans WHERE detail_pembayarans.pembayaran_id = pembayarans.id AND status_verifikasi = 'approved') >= pembayarans.nominal"))
            ->when(($filters['status'] ?? null) === 'belum_lunas', fn ($query) => $query->whereRaw("(SELECT COALESCE(SUM(jumlah_bayar), 0) FROM detail_pembayarans WHERE detail_pembayarans.pembayaran_id = pembayarans.id AND status_verifikasi = 'approved') < pembayarans.nominal"));
    }

    private function queueNotifications($sppIds, User $sender, string $source, array $filters = []): int
    {
        $ids = collect($sppIds)->unique()->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        $jobs = $ids->map(fn (int $id) => new SendSppBillingNotification($id, $sender->id))->all();
        $log = SppNotificationLog::create([
            'sent_by' => $sender->id,
            'recipient_count' => count($jobs),
            'source' => $source,
            'filters' => $filters ?: null,
            'sent_at' => now(),
        ]);
        $batch = Bus::batch($jobs)->name('Notifikasi Tagihan Pembayaran')->dispatch();
        $log->update(['batch_id' => $batch->id]);

        return count($jobs);
    }

    private function currentSppUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 403);

        $user->loadMissing('role');

        abort_unless(
            $this->canManageSpp($user) || $user->hasRole('Kepsek'),
            403,
        );

        return $user;
    }

    private function currentSppManager(): User
    {
        $user = $this->currentSppUser();

        abort_unless($this->canManageSpp($user), 403);

        return $user;
    }

    private function canManageSpp(User $user): bool
    {
        return $user->hasRole('Admin') || $user->hasRole('Staff Administrasi');
    }
}
