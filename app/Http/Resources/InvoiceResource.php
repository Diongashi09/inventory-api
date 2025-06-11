<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ClientResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\InvoiceItemResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            // 'logs' => $this->logs->map(function ($log) {
            //     return [
            //         'message' => $log->message,
            //         'created_at' => $log->created_at->toDateTimeString(),
            //     ];
            // }),
            'reference_number' => $this->reference_number,
            'date' => $this->date,
            'customer_type' => $this->customer_type,
            'customer_id' => $this->customer_id,
            'created_by' => $this->created_by,
            'status' => $this->status,
            'id' => $this->id,
            'shipping' => [
                'company' => optional($this->shipping)->shipping_company,
                'cost' => optional($this->shipping)->shipping_cost,
                'status' => optional($this->shipping)->status,
                'tracking_id' => optional($this->shipping)->tracking_id,
            ],
            'client' => new ClientResource($this->whenLoaded('client')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
        ];
    }
}