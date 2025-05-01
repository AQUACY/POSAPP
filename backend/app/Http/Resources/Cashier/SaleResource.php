<?php

namespace App\Http\Resources\Cashier;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray($request)
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
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ],
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount_amount' => $item->discount_amount,
                    'tax_amount' => $item->tax_amount,
                    'total_amount' => $item->total_amount,
                    'inventory' => [
                        'id' => $item->inventory->id,
                        'name' => $item->inventory->name,
                        'sku' => $item->inventory->sku,
                        'barcode' => $item->inventory->barcode,
                    ]
                ];
            }),
            'cashier' => [
                'id' => $this->cashier->id,
                'name' => $this->cashier->name,
                'email' => $this->cashier->email,
            ]
        ];
    }
} 