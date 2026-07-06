<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Ilan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KazananBildirimi extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Ilan $ilan,
        public User $kazanan,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tebrikler! Müzayedeyi kazandınız — ' . $this->ilan->baslik,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.kazanan',
            with: [
                'ad' => $this->kazanan->name,
                'baslik' => $this->ilan->baslik,
                'tutar' => number_format((int) $this->ilan->guncel_teklif, 0, ',', '.') . ' ₺',
                'lotNo' => $this->ilan->lot_no,
            ],
        );
    }
}
