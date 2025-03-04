<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Notifications\EmailVerificationNotification;
use App\Notifications\UserWelcomeNotification;
use F9Web\ApiResponseHelpers;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    use ApiResponseHelpers;
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request The request object containing the registration data.
     * @return \Illuminate\Http\Response The response object with the registration result.
     */
    public function register(Request $request): JsonResponse
    {
        // Validate the registration request data
        $validator = $this->requestValidation($request->all());

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

            // Create a new user record in the database
            $user = User::create([
                'name' => $request->name,
                'firstname' => $request->firstname,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Automatically authenticate the newly created user
            Auth::login($user);

            // Send a verification code to the user's email address
            $this->sendVerificationCode($user);

            // Delete all existing tokens for the user
            $user->tokens()->delete();

            // Create a new API token for the user
            $token = $user->createToken('GAUGE_TOKEN')->plainTextToken;

            // Retrieve the user information using a resource class
            $userResponse = new UserResource($user);

            // Return a success response with the user data and the API token
            return $this->respondWithSuccess([
                'message'=>'A verification code has been sent to your email address.',
                'api_token' =>  $token,
                "data"=>$userResponse]);
        }
    }

    /**
     * This function validates the registration request data.
     *
     * @param array $request The request data to be validated.
     * @return \Illuminate\Contracts\Validation\Validator The validator instance.
     *
     * @throws \Illuminate\Validation\ValidationException If the validation fails.
     */
    public function requestValidation(array $request) {

        // Create a new validator instance with the given rules and custom messages.
        $validator = Validator::make($request, [
            'name' => 'bail|required|string|min:2|max:255',
            'firstname' => 'bail|required|string|min:2|max:255',
            'email' => 'bail|required|string|email|max:255|unique:users',
            'password' => 'bail|required|string|min:5|confirmed',
            'password_confirmation' => 'bail|required|string',
        ],[
            'name.required' => 'Surname is required',
            'name.string' => 'Surname must be letters',
            'name.min' => 'Surname must be more then 1 character',
            'firstname.min' => 'First name must be more then 1 character',
            'firstname.string' => 'First name must be letters',
            'firstname.required' => 'First name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Use an appropriate email address',
            'password.min' => 'Password must be more than 5 characters',
            'password.confirmed' => 'Passwords do not match',
        ]);

        // Return the validator instance.
        return $validator;
    }

    public function sendVerificationCode(User $user): void {
        EmailVerificationCode::where('email', '=', $user->email)->delete();

        //Generate verification code
        $code = strtoupper(Str::random(env('APP_CODE_GENERATION_LIMIT')));

        //Save verification code to database
        EmailVerificationCode::create([
            'email' => $user->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(60),
        ]);

        // Send verification email
        $user->notify(new EmailVerificationNotification($code));
    }

    //Resend Verification Code
    public function reSendVerificationCode(Request $request): JsonResponse {
        $user = $request->user();
        if (!$user->email_verified_at) {
            $this->sendVerificationCode($user);
            return $this->respondWithSuccess([
                'sent' => true,
                'message'=>'A verification code has been sent to your email address.'
            ]);
        }
        else {
            return $this->respondWithSuccess([
                'sent' => false,
                'message'=>'Your email address has already been verified.'
            ]);
        }
    }

    /**
     * Confirm email verification
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmEmailVerification(Request $request): JsonResponse
    {
        //Check if user is already verified
        if ($request->user()->email_verified_at) {
            return $this->respondWithSuccess([
                'verified' => true,
                'message'=>'Your email address has already been verified.'
            ]);
        }

        //Check if verification code is valid
        $valid = EmailVerificationCode::where('email', '=', $request->user()->email)
            ->where('code', '=', $request->code)
            ->whereDate('expires_at', '<=', date('Y-m-d H:i:s', strtotime(now())))
            ->first();

        if ($valid) {
            $user = $request->user();
            $user->email_verified_at = now();
            $user->save();
            $valid->delete();

            //Send welcome email
            $user->notify(new UserWelcomeNotification($user));

            return $this->respondWithSuccess([
                'message' => 'Your email address has been verified.',
                'data' =>  UserResource::make($user)
            ]);
        } else {
            return $this->respondError('Expired or invalid verification code.');
        }
    }
}
