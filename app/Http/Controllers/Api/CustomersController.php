<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Sale\CustomersFullResource;
use App\Http\Resources\Sale\CustomersResource;
use App\Models\Business;
use App\Models\Customer;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomersController extends Controller
{
    use ApiResponseHelpers;

    public function getCustomers(Request $request) {
        // Fetch customers from your database
        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }
        // Return a JSON response with the customers
        $customers = CustomersResource::collection(Customer::where('business_id', $business_id)->get());
        return $this->respondWithSuccess(['data' => $customers]);
    }

    public function getCustomerById(Request $request,$id) {
        // Fetch customers from your database
        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }
        //Check if customer exists
        $customer = Customer::where('id', $id)->where('business_id', $business_id)->first();

        if(!$customer) {
            return $this->respondError('Customer not found');
        }
        // Return a JSON response with the customer
        $customer = CustomersFullResource::make($customer);
        return $this->respondWithSuccess($customer);
    }

    public function saveCustomer(Request $request) {

        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        $validator = Validator::make($request->all(), [
            'first_name' => [
                'bail','required','min:2','string'
            ],
            'last_name' => [
                'bail','required','min:2','string'
            ],
            'email' => [
                'bail','nullable','email',
            ],
            'phone' => [
                'bail','nullable','min:10','string',
            ],
            'address' => [
                'bail','nullable','min:8','string',
            ],
            'city' => [
                'bail','nullable','min:2','string',
            ],
            'state' => [
                'bail','nullable','min:2','string',
            ],
            'country' => [
                'bail','nullable','min:2','string',
            ],
            'company_name' => [
                'bail','nullable','min:2','string',
            ],
            'contact_person' => [
                'bail','nullable','min:2','string',
            ],
            'additional_info' => [
                'bail','nullable','min:2','string',
            ],
        ],[
            'first_name.required' => 'First name is required',
            'first_name.min' => 'First name should be at least 3 characters',
            'last_name.required' => 'Last name is required',
            'last_name.min' => 'Last name should be at least 3 characters',
            'email.email' => 'Invalid email is required',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        if($request->id){
            $customer = Customer::find($request->id);
            $customer->business_id = $business_id;
        }
        else {
            $customer = new Customer();
            $customer->business_id = $business_id;
        }

        if(!$customer){
            return $this->respondError('Invalid Customer');
        }

        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        $customer->city = $request->city;
        $customer->state = $request->state;
        $customer->country = $request->country;
        $customer->company_name = $request->company_name;
        $customer->contact_person = $request->contact_person;
        $customer->additional_info = $request->additional_info;
        
        if($customer->save()) {
            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'Customer was successfully updated',
            'customer' => CustomersResource::make($customer)
        ]);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function deleteCustomer(Request $request) {
        $business_id = $request->user()->business_id;

        $customer = Customer::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$customer) {
            return $this->respondError('Customer not found');
        }

        if($customer->salesOrders->count() > 0) {
            return $this->respondError('Customer cannot be deleted');
        }

        if($customer->delete()){
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Customer has been deleted.']);
        }
        else {
            return $this->respondError('Customer was not deleted.');
        }
        
    }
}
