<?php

namespace App\Jobs;

use App\Models\Spp;
use App\Notifications\SppBillingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class SendSppBillingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(private readonly int $sppId, private readonly int $senderId) {}

    public function handle(): void
    {
        $spp = Spp::with(['siswa.orangTua', 'siswa.kelas'])
            ->withSum('approvedPayments as total_dibayar', 'jumlah_bayar')
            ->findOrFail($this->sppId);
        $parent = $spp->siswa?->orangTua;

        if (! $parent || ! $parent->email) {
            return;
        }

        Notification::sendNow($parent, new SppBillingNotification($spp, (float) ($spp->total_dibayar ?? 0)));

        $spp->update([
            'last_notification_at' => now(),
            'last_notified_by' => $this->senderId,
        ]);
    }
}
