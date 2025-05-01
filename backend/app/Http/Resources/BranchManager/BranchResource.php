<?php

namespace App\Http\Resources\BranchManager;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'settings' => $this->settings,
            'is_active' => $this->is_active,
            'business' => [
                'id' => $this->business->id,
                'name' => $this->business->name,
                'logo' => $this->business->logo,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 