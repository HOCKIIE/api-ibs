<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'description_th' => $this->description_th,
            'description_en' => $this->description_en,
            'description_ja' => $this->description_ja,
            'status' => $this->status,
            'brand' => BrandResource::collection($this->whenLoaded('brand')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
