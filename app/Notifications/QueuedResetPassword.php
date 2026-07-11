<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class QueuedResetPassword extends ResetPassword implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Password Akun')
            ->view('mail.school.notification', [
                'subject' => 'Reset Password Akun',
                'preheader' => 'Tautan aman untuk mengatur ulang password akun Anda.',
                'heading' => 'Reset Password Akun',
                'recipientName' => $notifiable->name,
                'lines' => [
                    'Kami menerima permintaan untuk mengatur ulang password akun Anda.',
                    'Klik tombol di bawah ini untuk membuat password baru.',
                ],
                'actionText' => 'Reset Password',
                'actionUrl' => $this->resetUrl($notifiable),
                'details' => [],
                'note' => 'Jika Anda tidak melakukan permintaan ini, abaikan email ini.',
            ]);
    }
}
