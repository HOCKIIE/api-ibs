<?php

namespace App\Http\Resources;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        // $this->website ? url('/api/proxy?url')."=".$this->website : null
        return [
            'id' => $this->id,
            'image' => $this->image,
            'title_th' => $this->title_th,
            'title_en' => $this->title_en,
            'title_ja' => $this->title_ja,
            'description_th' => $this->description_th,
            'description_en' => $this->description_en,
            'description_ja' => $this->description_ja,
            'detail_th' => $this->detail_th,
            'detail_en' => $this->detail_en,
            'detail_ja' => $this->detail_ja,
            'website' => $this->website,
            'apiName' => $this->apiName,
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'category' => $this->whenLoaded('categories', function(){
                return $this->category->pluck('id');
            }),
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
