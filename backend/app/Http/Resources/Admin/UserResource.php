<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'role' => $this->role,
            'status' => $this->status,
            'branch_id' => $this->branch_id,
            'business_id' => $this->business_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'branch' => new BranchResource($this->whenLoaded('branch')),
            'business' => new BusinessResource($this->whenLoaded('business')),
            'performance_metrics' => $this->when(isset($this->performance_metrics), $this->performance_metrics),
        ];
    }
} 