<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ProductSummaryResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->transaction_type, // for frontend
            'transaction_type' => $this->transaction_type, // for clarity
            'reference_id' => $this->reference_id,
            'product' => new ProductSummaryResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'direction' => $this->direction,
            'tariff_fee' => $this->tariff_fee,
            'import_cost' => $this->import_cost,
            'created_at' => $this->created_at,
            'creator' => new UserResource($this->whenLoaded('creator')),
        ];
    }
}