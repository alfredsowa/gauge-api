<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Business\BusinessResource;
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
            'firstname' => $this->firstname,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'avatar_url' => $this->avatar_url,
            'business_id' => $this->business_id,
            'guide' => $this->guide,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'verified' => $this->when(!is_null($this->email_verified_at),true,false),
            'business' => BusinessResource::make($this->whenLoaded('business')),
        ];
    }
}
