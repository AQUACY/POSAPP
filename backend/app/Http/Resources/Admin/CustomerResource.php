<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'business_id' => $this->business_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'total_purchases' => $this->whenCounted('sales'),
            'total_spent' => $this->when(isset($this->total_spent), $this->total_spent),
            'last_purchase' => $this->when(isset($this->last_purchase), $this->last_purchase),
            'business' => new BusinessResource($this->whenLoaded('business')),
            'sales' => SaleResource::collection($this->whenLoaded('sales')),
        ];
    }
} 