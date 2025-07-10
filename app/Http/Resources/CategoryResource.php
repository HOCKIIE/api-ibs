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
        $keyword = request()->keyword;
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
            'brand' => $this->whenLoaded('brand',function() use($keyword) {
                return BrandResource::collection(
                    collect($this->brand)->filter(function ($brand) use ($keyword) {
                        return !$keyword 
                        || stripos($brand->title_th, $keyword) !== false
                        || stripos($brand->title_en, $keyword) !== false
                        || stripos($brand->title_ja, $keyword) !== false;
                    })
                );
            }),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
