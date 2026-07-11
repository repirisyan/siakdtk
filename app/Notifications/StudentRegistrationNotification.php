<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300, 900];

    public function __construct(
        private readonly string $studentName,
        private readonly string $status,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        [$subject, $heading, $lines, $note] = match ($this->status) {
            'approved' => ['Pendaftaran Disetujui', 'Pendaftaran Disetujui', ["Data pendaftaran {$this->studentName} telah disetujui oleh pihak sekolah.", 'Akun sekarang dapat digunakan untuk mengakses sistem setelah email terverifikasi.'], null],
            'rejected' => ['Pendaftaran Memerlukan Perbaikan', 'Pendaftaran Memerlukan Perbaikan', ["Data pendaftaran {$this->studentName} belum dapat disetujui.", 'Silakan periksa catatan dari pihak sekolah dan lakukan perbaikan yang diperlukan.'], 'Silakan hubungi pihak sekolah untuk informasi lebih lanjut.'],
            default => ['Pendaftaran Berhasil', 'Pendaftaran Berhasil', ["Data pendaftaran {$this->studentName} telah berhasil diterima.", 'Saat ini data sedang menunggu proses verifikasi oleh pihak sekolah.'], 'Kami akan menginformasikan perkembangan status pendaftaran melalui sistem.'],
        };

        return (new MailMessage)
            ->subject($subject)
            ->view('mail.school.notification', [
                'subject' => $subject,
                'preheader' => $subject,
                'heading' => $heading,
                'recipientName' => $notifiable->name,
                'lines' => $lines,
                'actionText' => null,
                'actionUrl' => null,
                'details' => ['Nama Siswa' => $this->studentName],
                'note' => $note,
            ]);
    }
}
