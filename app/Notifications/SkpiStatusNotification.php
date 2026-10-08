<?php

namespace App\Notifications;

use App\Models\SkpiRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SkpiStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public SkpiRequest $skpiRequest,
        public string $statusMessage,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $issued = $this->skpiRequest->status === 'issued';
        $rejected = $this->skpiRequest->status === 'rejected';

        $message = (new MailMessage)
            ->subject($issued ? 'SKPI Anda Telah Diterbitkan' : ($rejected ? 'Permintaan SKPI Belum Disetujui' : 'Status Pengajuan SKPI'))
            ->greeting('Halo, '.$notifiable->name.'.')
            ->line($this->statusMessage);

        if ($issued) {
            $message->line('Nomor dokumen: '.$this->skpiRequest->document_number)
                ->action('Buka dan unduh SKPI', route('student.skpi'));
        } else {
            $message->action('Periksa pengajuan SKPI', route('student.skpi'));
        }

        return $message->line('Pesan ini dikirim otomatis oleh sistem SKEM dan SKPI Politeknik Semen Indonesia.');
    }
}
