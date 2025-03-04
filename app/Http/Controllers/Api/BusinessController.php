<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Business\BusinessListResource;
use App\Http\Resources\Business\BusinessResource;
use App\Http\Resources\Business\OverheadResource;
use App\Models\Business;
use App\Models\Employee;
use App\Models\GeneralFeedback;
use App\Models\Overhead;
use App\Notifications\BusinessAccountDeletion;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class BusinessController extends Controller
{

    use ApiResponseHelpers;

    /**
     * Get the list of paginated businesses.
     *
     * @param \Illuminate\Http\Request $request The request object containing any necessary parameters.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     *         Returns a paginated collection of businesses as a JSON resource.
     *         If there are no businesses, returns a 204 No Content response.
     *
     * @throws \Illuminate\Database\QueryException If there is an error executing the database query.
     * @throws \InvalidArgumentException If the page size is not a positive integer.
     */
    public function getBusinesses(Request $request) {
        return BusinessListResource::collection(Business::withCount('suppliers')->paginate(15));
    }

    public function getDefaultBusiness(Request $request) {

        // Check if the business exists
        if ($business = Business::find($request->user()->business_id)) {
            // If the business exists, return it as a JSON resource
            $business = new BusinessResource($business);

            return $this->respondWithSuccess($business);
        }

        // If the business does not exist, return a 204 No Content response
        return $this->respondNoContent(null);
    }

    /**
     * Get a single business by its ID.
     *
     * @param int $id The ID of the business to retrieve.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the business with the given ID does not exist.
     */
    public function getBusiness($id) {

        // Check if the business exists
        if ($business = Business::find($id)) {
            // If the business exists, return it as a JSON resource
            $business = new BusinessResource($business);

            return $this->respondWithSuccess($business);
        }

        // If the business does not exist, return a 204 No Content response
        return $this->respondNoContent(null);
    }

    public function setBusinessDetails(Request $request) {

        $request_id = $request->id;

        if($request->user()->business_id > 0) {
            $request_id = $request->user()->business_id;
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'bail','required','min:3',
                Rule::unique('businesses','name')->ignore($request_id),
            ],
            'email' => [
                'bail','required','email',
                Rule::unique('businesses','email')->ignore($request_id),
            ]
        ],[
            'name.required' => 'Business name is required',
            'name.min' => 'Business name should be at least 3 characters',
            'name.unique' => 'Business name already exists',
            'email.required' => 'Email address is required',
            'email.email' => 'Invalid email address',
            'email.unique' => 'Email address already exists',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        try {
            if($request->id) {
                $business = Business::find($request->id);
                if(!$business) {
                    return $this->respondError('Invalid Business');
                }
                $business->name = $request->name;
                $business->email = $request->email;
                $business->save();
                return $this->getBusiness($business->id);
            }
            else {
                $business = Business::updateOrCreate([
                    'name' => $request->name,
                    'email' => $request->email],
                    [
                    'country' => env('APP_DEFAULT_COUNTRY'),
                    'timezone' => env('APP_DEFAULT_TIMEZONE'),
                    'language' => env('APP_DEFAULT_LANGUAGE'),
                    'currency' => env('APP_DEFAULT_CURRENCY'),
                ]);

                $request->user()->business_id = $business->id;
                $request->user()->save();

                Employee::create([
                    'business_id' => $business->id,
                    'first_name' => $request->user()->firstname,
                    'last_name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'hire_date' => date('Y-m-d'),
                ]);

                return $this->getBusiness($business->id);
            }
        }
        catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }

    }

    public function setBusinessComponents(Request $request) {

        $validator = Validator::make($request->all(), [
            'business_type'=>'bail|required|string',
            'business_size'=>'bail|required|string',
            'industry'=>'bail|required|string',
        ],[
            'business_type.required' => 'Business type is required',
            'business_size.required' => 'Business size is required',
            'industry.required' => 'Business size is required',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }


        try {
            $business = Business::find($request->id);
            if(!$business) {
                return $this->respondError('Invalid Business');
            }
            $business->business_type = $request->business_type;
            $business->business_size = $request->business_size;
            $business->industry = $request->industry;
            $business->save();
            return $this->getBusiness($business->id);
        }
        catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }

    }

    public function setBusinessLocation(Request $request) {

        $validator = Validator::make($request->all(), [
            'country'=>'bail|required|string',
            'city'=>'bail|required|string',
            'currency'=>'bail|required|string',
        ],[
            'country.required' => 'Country is required',
            'city.required' => 'City is required',
            'currency.required' => 'Currency is required',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        try {
            $business = Business::find($request->id);
            if(!$business) {
                return $this->respondError('Invalid Business');
            }
            $business->country = $request->country;
            $business->city = $request->city;
            $business->currency = $request->currency;
            $business->currency_symbol = (config('options.currency_symbol.'.$request->currency) != null)
                ?config('options.currency_symbol.'.$request->currency):'GH₵';
            if ($business->save()) {
                return $this->getBusiness($business->id);
            }
            else {
                return $this->respondError("Could not save information. Please try again.");
            }

        }
        catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }

    }

    public function setBusinessCompleted(Request $request) {

        try {
            $business = Business::find($request->id);
            if(!$business) {
                return $this->respondError('Invalid Business');
            }
            $business->setup = true;
            $business->save();

            $request->user()->guide = json_encode(config('options.modules'));
            $request->user()->save();

            return $this->getBusiness($business->id);

        } catch (\Throwable $e) {
            return $this->respondError($e->getMessage());
        }

    }

    public function setBusinessOtherDetails(Request $request) {

        $validator = Validator::make($request->all(), [
            'contact' => ['bail','string','nullable','min:10'],
            'address' => ['bail','string','nullable','min:5'],
            'language' => ['bail','required','string','min:3'],
            'website' => ['bail','string','nullable','min:5'],
            'tax_identification_number' => ['bail','string','nullable','min:5'],
        ],[
            'language.required' => 'Default Language is required',
            'language.min' => 'Language should be at least 3 characters',
            'contact.min' => 'Business Contact should be at least 10 characters',
            'address.min' => 'Business Address should be at least 5 characters',
            'website.min' => 'Business Website should be at least 5 characters',
            'tax_identification_number.min' => 'Business Tax ID should be at least 5 characters',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        try {
            if($request->id) {
                $business = Business::find($request->id);
                if(!$business) {
                    return $this->respondError('Invalid Business');
                }
                $business->language = $request->language;
                $business->contact = $request->contact;
                $business->address = $request->address;
                $business->website = $request->website;
                $business->tax_identification_number = $request->tax_identification_number;
                $business->save();
                return $this->getBusiness($business->id);
            }
        }
        catch (\Exception $e) {
            return $this->respondError($e->getMessage());
        }

    }

    public function removeBusiness(Request $request) {

        $business = Business::find($request->id);

        if ($business) {

            $log_message = "Business #".$business->id." has been deleted.";
            if($request->comment) {

                GeneralFeedback::create([
                    'from' => $request->user()->email.": ".$business->name." (ID: ".$business->id.")",
                    'type' => "Business Deletion",
                    'feedback' => $request->comment,
                ]);

                $log_message = "Business #".$business->id." has been deleted. Reason: ".$request->comment;
            }

            if ($business->delete()) {
                $request->user()->business_id = null;
                $request->user()->save();

                $request->user()->notify(new BusinessAccountDeletion($business->name));
                Log::channel('slack_account_deletion')->info($log_message);
                return $this->respondWithSuccess(['removed' => true,'message'=>'Business Deletion Complete']);
            }
            else {
                return $this->respondError('Something went wrong. Please try again');
            }

        }
        else {
            return $this->respondError('Invalid business');
        }

    }

    public function saveProfilePhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => ['bail','required',File::image()->max(2024),'mimes:jpg,jpeg,png'],
        ],[
            'file.required' => 'Business Logo is required',
            'file.mimes' => 'Image must be of types JPG, PNG, JPEG',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        try {
            $business = Business::find($request->user()->business_id);
            $path = $request->file('file')->store('business-logos','public');
            $business->logo = env('APP_URL').'storage/'.$path;
            $business->save();

            return $this->respondWithSuccess([
            'message'=>'Business Logo updated',
            'path'=>$business->logo,
            'saved' =>  true]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }

    }

    public function getOverheads(Request $request) {

        // Check if the business exists
        if ($business = Business::find($request->user()->business_id)) {
            // If the business exists, return it as a JSON resource
            return $this->respondWithSuccess(OverheadResource::collection($business->overheads));
        }

        // If the business does not exist, return a 204 No Content response
        return $this->respondNoContent(null);
    }

    public function removeOverhead(Request $request) {
        $business = Business::find($request->user()->business_id);
        // Check if the business exists
        if (!$business) {
            // If the business does not exists, return it as a JSON resource
            return $this->respondError('Invalid business');
        }

        try {
            
            $overhead = Overhead::find($request->id);
            if (!$overhead) {
                return $this->respondError('Invalid Overhead');
            }
            $overhead->delete();
            return $this->respondWithSuccess([
               'message'=>'Overhead removed',
               'saved' =>  true,
                'overhead' => OverheadResource::collection($business->overheads),
            ]);

        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }

    public function saveOverhead(Request $request) {

        $business = Business::find($request->user()->business_id);
        if (!$business) {
            // If the business does not exists, return it as a JSON resource
            return $this->respondError('Invalid business');
        }

        $validator = Validator::make($request->all(), [
            'title' => ['bail','required','string','min:2'],
            'cost' => ['bail','required','numeric','min:0'],
        ],[
            'title.required' => 'Title is required',
            'title.min' => 'Title is too short',
            'cost.required' => 'Cost is required',
            'cost.numeric' => 'Cost should be a number',
            'cost.min' => 'Cost must be positive or zero',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        try {
            Overhead::updateOrCreate([
                'title' => $request->title,
                'business_id' => $request->user()->business_id,
            ],[
                'cost' => $request->cost,
                'user_id' => $request->user()->id,
            ]);

            return $this->respondWithSuccess([
            'message'=>'Overhead saved',
            'saved' =>  true,
            'overhead' => OverheadResource::collection($business->overheads),
    
        ]);

        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }

    public function saveAverageMonthlyGoods(Request $request) {
        $validator = Validator::make($request->all(), [
            'average_goods_monthly' => ['bail','required','numeric','min:0'],
        ],[
            'average_goods_monthly.required' => 'Title is required',
            'average_goods_monthly.min' => 'Average must be positive or zero',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        try {
            $business = Business::find($request->user()->business_id);
            $business->average_goods_monthly = $request->average_goods_monthly;
            $business->save();

            return $this->getBusiness($business->id);

        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }

    public function removeProfilePhoto(Request $request){
        try {
            $business = Business::find($request->user()->business_id);
            $business->logo = null;
            $business->save();

            return $this->respondWithSuccess([
            'message'=>'Business Logo removed',
            'saved' =>  true]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }
}
