<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GeneralFeedback;
use App\Notifications\AccountDeactivationNotification;
use App\Notifications\AccountDeletionNotification;
use App\Notifications\ChangeEmailNotification;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\File as RulesFile;

class ProfileController extends Controller
{
    use ApiResponseHelpers;

    public function savePersonalDetails (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'bail|required|string|min:2|max:255',
            'firstname' => 'bail|required|string|min:2|max:255',
            'phone' => 'bail|required|string',
            'country' => 'bail|required|string',
        ],[
            'name.required' => 'Surname is required',
            'name.string' => 'Surname must be letters',
            'name.min' => 'Surname must be more then 1 character',
            'firstname.min' => 'First name must be more then 1 character',
            'firstname.string' => 'First name must be letters',
            'firstname.required' => 'First name is required',
            'phone.required' => 'Phone number is required',
            'country.required' => 'Which country are you in?',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        // If validation passes, proceed with the registration process
        if($validator->validated()) {
            $user = $request->user();
            $user->name = $request->name;
            $user->firstname = $request->firstname;
            $user->phone = $request->phone;
            $user->country = $request->country;

            if ($user->save()) {
                return $this->respondWithSuccess([
                    'message'=>'Personal details updated',
                    'saved' =>  true]);
            }
            else {
                return $this->respondError('Unable to save information. Try again.');
            }
        }
    }

    public function saveProfilePhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => ['bail','required',RulesFile::image()->max(2024),'mimes:jpg,jpeg,png'],
        ],[
            'file.required' => 'Profile Photo is required',
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
            $path = $request->file('file')->store('avatars','public');
            $request->user()->avatar_url = env('APP_URL').'storage/'.$path;
            $request->user()->save();

            return $this->respondWithSuccess([
            'message'=>'Profile photo updated',
            'path'=>$request->user()->avatar_url,
            'saved' =>  true]);
        } catch (\Throwable $th) {
            // return response()->json($th->getMessage());
            return $this->respondError($th->getMessage());;
        }
        
    }

    public function removeProfilePhoto(Request $request){
        try {
            $request->user()->avatar_url = null;
            $request->user()->save();

            return $this->respondWithSuccess([
            'message'=>'Profile photo removed',
            'saved' =>  true]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }

    public function changeEmailAddress (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required',
        ],[
            'email.required' => 'Email Address is required',
            'email.email' => 'Invalid email address',
            'email.unique' => 'This email address is already',
            'password.required' => 'Password is required',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        // If validation passes, proceed with the registration process
        if($validator->validated()) {
            $user = $request->user();

            if (! Hash::check($request->password, $user->password)) {
                return $this->respondError('Invalid password');
            }

            $user->email = $request->email;

            if ($user->save()) {
                $user->notify(new ChangeEmailNotification());
                return $this->respondWithSuccess([
                    'message'=>'Email address updated',
                    'saved' =>  true]);
            }
            else {
                return $this->respondError('Unable to update email address. Try again.');
            }
        }
    }

    public function updatePassword (Request $request) {
        $validator = Validator::make($request->all(), [
            'current_password' => 'bail|required|string|min:6',
            'password' => 'bail|required|string|min:6|confirmed',
            'password_confirmation' => 'bail|required',
        ],[
            'current_password.required' => 'Curent Password is required',
            'current_password.min' => 'Curent Password must be at least 6 characters',
            'new_password.required' => 'New Password is required',
            'new_password.min' => 'New Password must be at least 6 characters',
            'password_confirmation.required' => 'New Password is required',
            'password.confirmed' => 'Confirm Password does not match new password',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        // If validation passes, proceed with the registration process
        if($validator->validated()) {
            $user = $request->user();

            $user->password = Hash::make($request->password);

            if ($user->save()) {
                return $this->respondWithSuccess([
                    'message'=>'Password is updated',
                    'saved' =>  true]);
            }
            else {
                return $this->respondError('Unable to update password. Try again.');
            }
        }
    }

    public function deactivateAccount (Request $request) {

        $user = $request->user();

        if($request->feedback) {

            GeneralFeedback::create([
                'from' => $user->email,
                'type' => $request->disableAccount?'Account Deactivation':'Account Deletion',
                'feedback' => $request->feedback,
            ]);

        }

        if(!is_null($request->disableAccount)) {
            $message = "3";
            if($request->disableAccount) {

                $user->deactivated_at = now(); 

                if ($user->save()) {
                    $user->notify(new AccountDeactivationNotification());
                    $message = "Account has been deactivated.";
                }

            }
            else {
                $log_message = "Account #".$user->id." has deleted their account.";
                $old_user = $user;
                if ($user->delete()) {
                    $old_user->notify(new AccountDeletionNotification());
                    Log::channel('slack_account_deletion')->info($log_message);
                    $message = "Account has been deleted.";
                }

            }

            return $this->respondWithSuccess(['deactivated' => true,'message'=>$message]);
        }
        
        return $this->respondError('Unable to take action on account. Try again.');
    }
}
