<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
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
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'branch_id' => $this->branch_id,
            'cashier_id' => $this->cashier_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'cashier' => new UserResource($this->whenLoaded('cashier')),
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
        ];
    }
} 