<?php

namespace App\Notifications;

use App\Models\Spp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SppBillingNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Spp $spp, private readonly float $totalPaid) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $siswa = $this->spp->siswa;
        $sisa = max((float) $this->spp->nominal - $this->totalPaid, 0);
        $rupiah = fn (float $value) => 'Rp'.number_format($value, 0, ',', '.');
        $status = $sisa <= 0 ? 'Lunas' : 'Belum Lunas';
        $subject = 'Tagihan Pembayaran Anak Anda';

        return (new MailMessage)
            ->subject($subject)
            ->view('mail.school.notification', [
                'subject' => $subject,
                'preheader' => "Tagihan {$this->spp->jenis_pembayaran} untuk {$siswa->nama} tersedia.",
                'heading' => 'Tagihan Pembayaran Anak Anda',
                'recipientName' => $notifiable->name,
                'lines' => ['Tagihan pembayaran anak Anda telah tersedia. Silakan periksa detail dan lakukan pembayaran sebelum jatuh tempo.'],
                'actionText' => 'Lihat Tagihan',
                'actionUrl' => route('tagihan-saya.show', $this->spp),
                'details' => [
                    'Nama Siswa' => $siswa->nama,
                    'Kelas' => $siswa->kelas?->nama_kelas ?? '-',
                    'Tahun Ajaran' => $this->spp->thn_ajaran,
                    'Jenis Pembayaran' => $this->spp->jenis_pembayaran,
                    'Nominal Tagihan' => $rupiah((float) $this->spp->nominal),
                    'Sisa Tagihan' => $rupiah($sisa),
                    'Status' => $status,
                ],
                'note' => 'Abaikan email ini apabila tagihan telah dilunasi.',
            ]);
    }
}
