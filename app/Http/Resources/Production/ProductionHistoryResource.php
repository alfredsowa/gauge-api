<?php

namespace App\Http\Resources\Production;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductionHistoryResource extends JsonResource
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
            'status' => $this->status,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'user' => User::select('id','firstname', 'name','email','avatar_url')->where('id', $this->user_id)->first(),
        ];
    }
}
