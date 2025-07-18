<?php

namespace App\Mail;

// use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClientShippingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function build()
    {
        $clientName = $this->order->client->name;
        $referenceNumber = $this->order->reference_number;
        $shipping = $this->order->shipping;

        $shippingCompany = $shipping?->shipping_company ?? 'Unknown';
        $trackingId = $shipping?->tracking_id ?? 'N/A';
        $status = $shipping?->status ?? 'Pending';

    $textBody = <<<EOT
Hi {$clientName},

Your order  ({$referenceNumber}) is now on the way!

Shipping Details:
- Shipping Company: {$shippingCompany}
- Tracking ID: {$trackingId}
- Status: {$status}

Thank you for shopping with us.

Best regards,
Your Company Name
EOT;

    return $this->subject('Your order is on the way!')
                ->html(nl2br(e($textBody))); // Renders raw HTML with line breaks
}



    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Client Shipping Notification',
    //     );
    // }

    // /**
    //  * Get the message content definition.
    //  */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

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
