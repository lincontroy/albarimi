<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class UplineBonusEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $upline;
    public $downline;
    public $commissionAmount;
    public $packageAmount;
    public $packageName;

    /**
     * Create a new message instance.
     */
    public function __construct(User $upline, User $downline, $commissionAmount, $packageAmount, $packageName)
    {
        $this->upline = $upline;
        $this->downline = $downline;
        $this->commissionAmount = $commissionAmount;
        $this->packageAmount = $packageAmount;
        $this->packageName = $packageName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Congratulations! You Earned KES ' . number_format($this->commissionAmount) . ' Referral Bonus - Barimax Top',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.upline-bonus',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}