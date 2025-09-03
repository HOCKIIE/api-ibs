<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
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
            'logo' => $this->logo,
            'title_th' => $this->title_th,
            'title_en' => $this->title_en,
            'title_ja' => $this->title_ja,
            'address_th' => $this->address_th,
            'address_en' => $this->address_en,
            'address_ja' => $this->address_ja,
            'email' => $this->email,
            'phone' => $this->phone,
            'mobile' => $this->mobile,
            'gmap' => $this->gmap,
        ];
    }
}
