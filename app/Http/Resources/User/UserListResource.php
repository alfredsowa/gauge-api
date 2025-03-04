<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Business\BusinessListResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserListResource extends JsonResource
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
            'setup' => $this->setup,
            'created_at' => $this->created_at,
            'business' => BusinessListResource::make($this->whenLoaded('business')),
        ];
    }
}
