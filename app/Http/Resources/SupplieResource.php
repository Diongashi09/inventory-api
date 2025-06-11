<?php

namespace App\Http\Resources;

use App\Http\Resources\ClientResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\SupplieItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'reference_number'  => $this->reference_number,
            'date'             => $this->date->toIso8601String(),
            'supplier_type'    => $this->supplier_type,
            'supplier_id'      => $this->supplier_id,
            'tariff_fee'       => $this->tariff_fee,
            'import_cost'      => $this->import_cost,
            'status'           => $this->status,
            'created_by'       => $this->created_by,
            'client'           => new ClientResource($this->whenLoaded('client')),
            'creator'          => new UserResource($this->whenLoaded('creator')),
            'items' => SupplieItemResource::collection($this->whenLoaded('items')),
            'created_at'       => $this->created_at->toIso8601String(),
            'updated_at'       => $this->updated_at->toIso8601String(),
        ];
    }
}