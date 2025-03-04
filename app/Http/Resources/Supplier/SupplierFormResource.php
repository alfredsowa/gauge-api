<?php

namespace App\Http\Resources\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierFormResource extends JsonResource
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
            'contact_person' => $this->contact_person,
            'company_name' => $this->company_name,
            'contact_detail' => $this->contact_detail,
            'location' => $this->location,
            'note' => $this->note,
        ];
    }
}
