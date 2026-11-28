<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminLoginApprovalMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $adminUser,
        public string $approveUrl,
        public string $rejectUrl,
        public string $ipAddress,
        public string $userAgent,
        public string $expiresAtText
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Persetujuan Akses Login Admin - Lumbi Uwuu',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-login-approval',
        );
    }
}