<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sale\SalesBasicResource;
use App\Http\Resources\Sale\SalesResource;
use App\Models\Business;
use App\Models\Product;
use App\Models\Sale;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SalesController extends Controller
{
    use ApiResponseHelpers;

    public function getSales(Request $request) {
        // Fetch sales from your database
        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }
        // Return a JSON response with the sales
        $sales = SalesBasicResource::collection(Sale::where('business_id', $business_id)->orderBy('sale_date_time','desc')->get());
        return $this->respondWithSuccess(['data'=>$sales]);
    }

    public function getSaleById(Request $request,$id) {
        // Fetch sales from your database
        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }
        //Check if sale exists
        $sale = Sale::where('id', $id)->where('business_id', $business_id)->first();

        if(!$sale) {
            return $this->respondError('Sale not found');
        }
        // Return a JSON response with the sale
        $sale = SalesResource::make($sale);
        return $this->respondWithSuccess($sale);
    }

    public function saveSale(Request $request) {

        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        $product = Product::where('business_id',$business_id)->where('id',$request->product_id)->first();

        if(!$product) {
            return $this->respondError('Product not found');
        }
        
        if($product->stock_quantity < $request->quantity) {
            return $this->respondError('The product quantity is too low. Only '.$product->stock_quantity.' available');
        }

        $validator = Validator::make($request->all(), [
            'product_id' => [
                'bail','required','min:1','numeric'
            ],
            'employee_id' => [
                'bail','required','min:1','numeric'
            ],
            'customer_id' => [
                'bail','required','min:1','numeric'
            ],
            'sale_type' => [
                'bail','required','min:3','string'
            ],
            'sales_channel' => [
                'bail','required','min:3','string'
            ],
            'sale_date_time' => [
                'bail','required','string',
            ],
            'quantity' => [
                'bail','required','min:1','numeric',
            ],
            // 'selling_price' => [
            //     'bail','required','min:0','numeric',
            // ],
            'total_amount_paid' => [
                'bail','required','min:0','numeric',
            ],
            'payment_status' => [
                'bail','required','min:3','string',
            ],
            'payment_method' => [
                'bail','required','min:3','string',
            ],
            'order_status' => [
                'bail','required','min:3','string',
            ],
            'invoice_number' => [
                'bail','nullable','min:2','string',
            ],
            'delivery_details' => [
                'bail','nullable','min:3','string',
            ],
        ],[
            'product_id.required' => 'A product is required. Select one',
            'product_id.numeric' => 'Invalid product selected',
            'employee_id.required' => 'Seller is required',
            'employee_id.numeric' => 'Invalid seller selected',
            'customer_id.required' => 'Customer is required',
            'customer_id.numeric' => 'Invalid customer selected',
            'sale_type.required' => 'Sale type is required',
            'sales_channel.required' => 'Sales channel is required',
            'sale_date_time.required' => 'Sale date and time is required',
            'quantity.required' => 'Quantity is required',
            'quantity.min' => 'Quantity must be more than zero',
            'selling_price.required' => 'Selling price is required',
            'selling_price.min' => 'Selling price must not be less than zero',
            'total_amount_paid.required' => 'Total amount paid is required',
            'total_amount_paid.min' => 'Total amount paid must not be less than zero',
            'payment_status.required' => 'Payment status is required',
            'payment_method.required' => 'Payment method is required',
            'order_status.required' => 'Order status is required',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $previous_status = '';
        $previous_quantity = 0;

        if($request->id){
            $sale = Sale::find($request->id);
            $sale->business_id = $business_id;

            $previous_status = $sale->order_status;
            $previous_quantity = $sale->quantity;
        }
        else {
            $sale = new Sale();
            $sale->business_id = $business_id;
        }

        if(!$sale){
            return $this->respondError('Invalid Sale');
        }

        $product_cost = $product->price * $request->quantity;
        $getCost = new ProductsController();
        $product_pricing = $getCost->getProductMaterialCostArray($request,$request->product_id);
        
        if($product->use_manual_pricing) {
            if($request->sale_type ==='retail') {
                $product_cost = $product->price * $request->quantity;
            } else {
                $product_cost = $product->wholesale_price * $request->quantity;
            }
        } else {
            $wholesale_price = $product_pricing['total_cost_of_goods'] * $product->wholesale_markup;
            $retail_price = $wholesale_price * $product->retail_markup;

            if($request->sale_type === 'retail') {
                $product_cost = $retail_price * $request->quantity;
            }else {
                $product_cost = $wholesale_price * $request->quantity;
            }
        }

        $sale->user_id = $request->user()->id;
        $sale->product_id = $request->product_id;
        $sale->employee_id = $request->employee_id;
        $sale->customer_id = $request->customer_id;
        $sale->sale_type = $request->sale_type;
        $sale->sales_channel = $request->sales_channel;
        $sale->sale_date_time = date('Y-m-d H:i:s', strtotime($request->sale_date_time));
        $sale->quantity = $request->quantity;
        $sale->selling_price = $product_cost;
        $sale->total_amount_paid = $request->total_amount_paid;
        $sale->payment_status = $request->payment_status;
        $sale->payment_method = $request->payment_method;
        $sale->order_status = $request->order_status;
        $sale->invoice_number = $request->invoice_number;
        $sale->delivery_details = $request->delivery_details;
        
        if($sale->save()) {
            
            if($previous_status == 'completed' || $sale->order_status == 'returned') {
                $product->stock_quantity += $previous_quantity;
                $product->save();
            }

            if($sale->order_status == 'completed') {
                $product->stock_quantity -= $request->quantity;
                $product->save();
            }
            
            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'Sale was successfully updated',
            'sale' => SalesResource::make($sale)
        ]);
        
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function deleteSale(Request $request) {
        $business_id = $request->user()->business_id;

        $sale = Sale::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$sale) {
            return $this->respondError('Sale not found');
        }

        if($sale->delete()){
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Sale has been deleted.']);
        }
        else {
            return $this->respondError('Sale was not deleted.');
        }
        
    }
}
