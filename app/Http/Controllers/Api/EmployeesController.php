<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Employee\EmployeeBasicResource;
use App\Http\Resources\Employee\EmployeeResource;
use App\Models\Business;
use App\Models\Employee;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class EmployeesController extends Controller
{
    use ApiResponseHelpers;

    public function getEmployees(Request $request) {

        $business_id = $request->user()->business_id;

        if(isset($request->business_id)) {
            $business_id = $request->business_id;
        }

        return $this->respondWithSuccess(['data' => EmployeeResource::collection(Employee::where('business_id',$business_id)->orderBy('created_at','desc')->get())]);
    }

    public function getBasicEmployee(Request $request) {

        $business_id = $request->user()->business_id;

        if(isset($request->business_id)) {
            $business_id = $request->business_id;
        }

        return $this->respondWithSuccess(EmployeeBasicResource::make(Employee::where('business_id',$business_id)->where('id',$request->id)->first()));
    }

    public function saveEmployee(Request $request) {

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
//                Rule::unique('employees','email')->ignore($request->id),
            ],
            'file' => ['bail','nullable',File::image()->max(5024),'mimes:jpg,jpeg,png'],
            'phone_number' => [
                'bail','required','min:10','string',
            ],
            'alt_phone_number' => [
                'bail','nullable','min:10','string',
            ],
            'hire_date' => [
                'bail','required','min:8','string',
            ],
            'title' => [
                'bail','nullable','min:3','string',
            ],
            'department' => [
                'bail','nullable','min:3','string',
            ],
        ],[
            'first_name.required' => 'First name is required',
            'first_name.min' => 'First name should be at least 3 characters',
            'last_name.required' => 'Last name is required',
            'last_name.min' => 'Last name should be at least 3 characters',
            // 'email.required' => 'Email is required',
            'email.email' => 'Invalid email is required',
//            'email.unique' => 'Email already exists',
            'phone_number.required' => 'Phone number is required',
            'phone_number.min' => 'Phone number must be 10 characters and more',
            'alt_phone_number.min' => 'Alternative Phone number must be 10 characters and more',
            'hire_date.required' => 'Hire date is required',
            'hire_date.min' => 'Incorrect date',
            // 'title.required' => 'Job title is required',
            'title.min' => 'Job title is too short',
            // 'department.required' => 'Department name is required',
            'department.min' => 'Department name is too short',
            'file.mimes' => 'Image must be of types JPG, PNG, JPEG',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        if($request->id){
            $employee = Employee::find($request->id);
            $employee->business_id = $business_id;
        }
        else {
            $employee = new Employee();
            $employee->business_id = $business_id;
        }

        if(!$employee){
            return $this->respondError('Invalid Employee');
        }

        $employee->first_name = $request->first_name;
        $employee->last_name = $request->last_name;
        $employee->email = $request->email;
        $employee->phone_number = $request->phone_number;
        $employee->alt_phone_number = $request->alt_phone_number;
        $employee->title = $request->title;
        $employee->hire_date = date('Y-m-d', strtotime($request->hire_date));
        $employee->department = $request->department;

        if($request->hasFile('file')) {
            $path = $request->file('file')->store('employee/'.$request->user()->business_id,'public');
            $employee->image = env('APP_URL').'storage/'.$path;
        }

        if($employee->save()) {
            return $this->respondWithSuccess(['saved' =>true,'message' =>'Employee was successfully updated']);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function deleteEmployee(Request $request) {
        $business_id = $request->user()->business_id;

        $employee = Employee::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$employee) {
            return $this->respondError('Employee not found');
        }

        if($employee->delete()){
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Employee has been deleted.']);
        }
        else {
            return $this->respondError('Employee was not deleted.');
        }

    }

    public function removePhoto(Request $request){
        try {
            $employee = Employee::where('id',$request->id)->where('business_id',$request->user()->business_id)->first();
            $employee->image = null;
            $employee->save();

            return $this->respondWithSuccess([
            'message'=>'Employee image removed',
            'saved' =>  true]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }
}
