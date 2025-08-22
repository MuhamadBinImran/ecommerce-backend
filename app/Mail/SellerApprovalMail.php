<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SellerApprovalMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $seller;
    public $approved;

    /**
     * Create a new message instance.
     */
    public function __construct($seller, $approved)
    {
        $this->seller = $seller;
        $this->approved = $approved;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject($this->approved ? 'Seller Account Approved' : 'Seller Account Unapproved')
            ->view('emails.seller.approval_html')
            ->with([
                'seller'   => $this->seller,
                'approved' => $this->approved,
            ]);

    }
}
