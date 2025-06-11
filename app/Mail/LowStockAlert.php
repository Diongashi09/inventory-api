<?php

namespace App\Mail;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockAlert extends Mailable
{
    use Queueable, SerializesModels;

    public $products;
    /**
     * Create a new message instance.
     */
    public function __construct(array $products)
    {
        $this->products = $products;
    }

    // public function build()
    // {
    //     $productName = $this->product->name;
    //     $availableQuantity = $this->product->quantity;  // adjust this based on how you retrieve the quantity
    //     $textBody = "Warning: Low stock for {$productName}. Available Quantity: {$availableQuantity}.";

    //     // Directly set the plain-text body
    //     return $this->subject("Low Stock Alert: {$productName}")
    //                 ->text('emails.raw') // Remove this if you don’t have a Blade file
    //                 ->with(['textBody' => $textBody]);
    // }

    public function build()
    {
        return $this->subject('Low Stock Alert')
                    ->view('emails.low_stock_alert')
                    ->with([
                        'products' => $this->products
                    ]);
    }


    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Low Stock Alert',
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
