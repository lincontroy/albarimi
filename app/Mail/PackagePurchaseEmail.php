<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Package;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PackagePurchaseEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $package;
    public $packageDetails;
    public $upline;
    public $commissionAmount;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Package $package, $packageDetails, $upline = null, $commissionAmount = 0)
    {
        $this->user = $user;
        $this->package = $package;
        $this->packageDetails = $packageDetails;
        $this->upline = $upline;
        $this->commissionAmount = $commissionAmount;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🚀 Your ' . $this->packageDetails['name'] . ' Package is Now Active! - Barimax Top',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.package-purchase',
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