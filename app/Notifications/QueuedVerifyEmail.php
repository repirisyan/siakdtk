<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Verifikasi Email Anda')
            ->view('mail.school.notification', [
                'subject' => 'Verifikasi Email Anda',
                'preheader' => 'Konfirmasi alamat email untuk akun SIAKDTK Anda.',
                'heading' => 'Verifikasi Email Anda',
                'recipientName' => $notifiable->name,
                'lines' => [
                    'Terima kasih telah melakukan pendaftaran pada Sistem Informasi Akademik Sekolah.',
                    'Silakan klik tombol di bawah ini untuk memverifikasi alamat email Anda.',
                ],
                'actionText' => 'Verifikasi Email',
                'actionUrl' => $this->verificationUrl($notifiable),
                'details' => [],
                'note' => 'Jika Anda tidak melakukan pendaftaran, abaikan email ini.',
            ]);
    }
}
