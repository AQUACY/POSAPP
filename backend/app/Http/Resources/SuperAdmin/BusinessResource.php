<?php

namespace App\Http\Resources\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\SuperAdmin\BranchResource;
use App\Http\Resources\SuperAdmin\UserResource;

/**
 * Resource for transforming business data
 */
class BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'logo' => $this->logo,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'branches' => BranchResource::collection($this->whenLoaded('branches')),
            'admin' => new UserResource($this->whenLoaded('admin')),
        ];
    }
} 