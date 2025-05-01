<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
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
            'status' => $this->status,
            'business_id' => $this->business_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'business' => new BusinessResource($this->whenLoaded('business')),
            'staff' => UserResource::collection($this->whenLoaded('staff')),
            'inventory' => InventoryResource::collection($this->whenLoaded('inventory')),
            'sales' => SaleResource::collection($this->whenLoaded('sales')),
            'performance_metrics' => $this->when(isset($this->performance_metrics), $this->performance_metrics),
        ];
    }
} 