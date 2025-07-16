<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray($req): array
    {
        return [
            'id'               => $this->id,
            'reference_number' => $this->reference_number,
            'date'             => $this->date->toDateString(),
            'status'           => $this->status,
            'customer'         => $this->client->name ?? null,
            'items'            => $this->items->map(fn($i)=>[
                'product'   => $i->product->name,
                'quantity'  => $i->quantity,
                'unit_price'=> $i->unit_price,
                'total'     => $i->total_price,
            ]),
        ];
    }
}
