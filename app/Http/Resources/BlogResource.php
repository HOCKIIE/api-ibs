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
            'name_th' => $this->name_th,
            'name_en' => $this->name_en,
            'name_jp' => $this->name_jp,
            'description_th' => $this->description_th,
            'description_en' => $this->description_en,
            'description_jp' => $this->description_jp,
            'deiail_th' => $this->deiail_th,
            'deiail_en' => $this->deiail_en,
            'deiail_jp' => $this->deiail_jp,
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
