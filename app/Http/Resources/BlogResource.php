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
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'description_th' => $this->description_th,
            'description_en' => $this->description_en,
            'description_ja' => $this->description_ja,
            'detail_th' => $this->detail_th,
            'detail_en' => $this->detail_en,
            'detail_ja' => $this->detail_ja,
            'status' => $this->status,
            'pathName' => $this->pathName,
            'published_at' => $this->published_at ? date_format(new \DateTime($this->published_at), 'F d, Y') : null,
            'is_deleted' => $this->is_deleted,
            'created_at' =>  $this->created_at ? date_format($this->created_at, 'd/m/Y H:i') : null,
            'updated_at' => $this->updated_at ? date_format($this->updated_at, 'd/m/Y H:i') : null,
            'deleted_at' => $this->deleted_at
        ];
    }
    
}
