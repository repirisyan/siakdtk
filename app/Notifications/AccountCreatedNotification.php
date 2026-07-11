<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun Sistem Sekolah Berhasil Dibuat')
            ->view('mail.school.notification', [
                'subject' => 'Akun Sistem Sekolah Berhasil Dibuat',
                'preheader' => 'Akun SIAKDTK Anda telah dibuat oleh pihak sekolah.',
                'heading' => 'Akun Sistem Sekolah Berhasil Dibuat',
                'recipientName' => $notifiable->name,
                'lines' => [
                    'Akun Anda telah berhasil dibuat oleh pihak sekolah.',
                    'Silakan gunakan email dan password yang diberikan oleh pihak sekolah untuk masuk ke sistem.',
                ],
                'actionText' => null,
                'actionUrl' => null,
                'details' => ['Email' => $notifiable->email],
                'note' => 'Demi keamanan akun, segera ganti password setelah login pertama.',
            ]);
    }
}
