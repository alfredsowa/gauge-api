<?php

namespace App\Http\Resources\Audit;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReconciliationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = User::withTrashed()->select('id', 'firstname', 'name','avatar_url','email')
                    ->where('id',$this->user_id)
                    ->where('business_id',$this->business_id)
                    ->first(); 
        return [
            'id'   => $this->id,
            'business_id'   => $this->business_id,
            'user_id'   => $this->user_id,
            'title'   => $this->title,
            'type'   => $this->type,
            'data'   => $this->data,
            'period'   => $this->period,
            'closed'   => $this->closed,
            'closed_on'   => $this->closed_on,
            'paused'   => $this->paused,
            'categories'   => $this->categories,
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
            'user'   => $user,
        ];
    }
}
