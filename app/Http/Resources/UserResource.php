<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
        // return [
        //     'id' => $this->id,
        //     'role' => $this->role,
        //     'title' => $this->title,
        //     'name' => $this->name,
        //     'phone' => $this->phone,
        //     'email' => $this->email,
        //     'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        //     'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        //     'meta' => {
        //         'current_page' => $this->resource->currentPage() ?? null,
        //         'last_page' => $this->resource->lastPage() ?? null,
        //         'per_page' => $this->resource->perPage() ?? null,
        //         'total' => $this->resource->total() ?? null,
        //     }
        // ];
    }
}
