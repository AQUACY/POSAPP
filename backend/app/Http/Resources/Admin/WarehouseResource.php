<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
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
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'business' => [
                'id' => $this->business->id,
                'name' => $this->business->name,
            ],
            'inventory_count' => $this->whenCounted('inventory'),
            'stock_requests_count' => $this->whenCounted('stockRequests'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 