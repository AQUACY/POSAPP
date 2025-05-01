<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\BusinessResource;
use App\Http\Resources\Admin\BranchResource;
use App\Http\Resources\Admin\UserResource;
use App\Http\Resources\Admin\SaleItemResource;
use App\Http\Resources\Admin\CustomerResource;

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
            'sale_number' => $this->sale_number,
            'total_amount' => $this->total_amount,
            'discount_amount' => $this->discount_amount,
            'tax_amount' => $this->tax_amount,
            'final_amount' => $this->final_amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'status' => $this->status,
            'business_id' => $this->business_id,
            'branch_id' => $this->branch_id,
            'cashier_id' => $this->cashier_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'business' => new BusinessResource($this->whenLoaded('business')),
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'cashier' => new UserResource($this->whenLoaded('cashier')),
            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
        ];
    }
} 