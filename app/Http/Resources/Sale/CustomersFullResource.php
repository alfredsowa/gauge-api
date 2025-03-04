<?php

namespace App\Http\Resources\Sale;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class CustomersFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $last_purchase = $this->salesOrders->sortByDesc('sale_date_time')->pluck('sale_date_time')->first();
        if($this->salesOrders->count() > 0) {
            $last_purchase_ = Carbon::createFromDate($last_purchase); 
            $last_purchase = $last_purchase_->diffForHumans();
        }
        else {
            $last_purchase = '-';
        }
        
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'state' => $this->state,
            'country' => $this->country,
            'company_name' => $this->company_name,
            'contact_person' => $this->contact_person,
            'additional_info' => $this->additional_info,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'last_purchase' => $last_purchase,
            'sales' => SalesBasicResource::collection($this->salesOrders),
            'purchases' => $this->salesOrders->count(),
            'prevent_delete' => ($this->salesOrders->count() > 0)?true:false,
        ];
    }
}
