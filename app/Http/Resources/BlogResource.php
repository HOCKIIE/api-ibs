<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'image' => $this->image,
            'title_th' => $this->title_th,
            'title_en' => $this->title_en,
            'title_ja' => $this->title_ja,
            'category' => $this->category,
            'description_th' => $this->description_th,
            'description_en' => $this->description_en,
            'description_ja' => $this->description_ja,
            'detail_th' => $this->detail_th,
            'detail_en' => $this->detail_en,
            'detail_ja' => $this->detail_ja,
            'status' => $this->status,
            'published_at' => $this->published_at,
            'is_deleted' => $this->is_deleted,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at
        ];
    }
    
}
