<?php

namespace App\Http\Resources\Sale;

use App\Http\Resources\Employee\EmployeeBasicResource;
use App\Http\Resources\Product\ProductBasicResource;
use App\Http\Resources\User\UserListResource;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $added_by = User::select('firstname', 'name')->where('id', '=', $this->user_id)->first();
        $sold_by = Employee::select('first_name', 'last_name')->where('id', '=', $this->employee_id)->first();
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'customer_id' => $this->customer_id,
            'product_id' => $this->product_id,
            'employee_id' => $this->employee_id,
            'user_id' => $this->user_id,
            'sale_type' => $this->sale_type,
            'sales_channel' => $this->sales_channel,
            'sale_date_time' => $this->sale_date_time,
            'quantity' => $this->quantity,
            'selling_price' => $this->selling_price,
            'total_amount_paid' => $this->total_amount_paid,
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'order_status' => $this->order_status,
            'invoice_number' => $this->invoice_number,
            'delivery_details' => $this->delivery_details,
            'customer' => CustomersResource::make(Customer::find($this->customer_id)),
            'added_by' => $added_by->firstname.' '.$added_by->name,
            'sold_by' => $sold_by->first_name.' '.$sold_by->last_name,
            'product' => ProductBasicResource::make($this->product),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
