<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Notifications\ConfirmPasswordResetNotification;
use App\Notifications\PasswordResetNotification;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    use ApiResponseHelpers;

    /**
     * Login via API endpoint
     *
     * @param Request $request The request object containing the user's email and password
     * @return array|Illuminate\Http\JsonResponse The API response containing the user's token and data if successful, or an error message if not
     */
    public function login (Request $request) {

        //Check if the user exists and get the user
        $user = User::where('email','=',$request->email)->first();

        //Does user exist
        if (isset($user)) {

            //Compare the user password to the current password provided in the request
            if (Hash::check($request->password, $user->password)) {

                //Logs the user in
                if (auth()->attempt(['email'=>$request->email,'password'=>$request->password])) {

                    $verified = false;
                    //Check if the user is already verified
                    if ($user->email_verified_at == null) {
                        $verified = false;
                    }

                    if($user->currentAccessToken()) {
                        $token = $user->currentAccessToken()->plainTextToken;
                    }
                    else {
                        //Delete all the user tokens
                        $user->tokens()->delete();
                        $token = $user->createToken(env('APP_NAME'))->plainTextToken;
                    }


                    // $userResponse = new UserResource($user);
                    $user->deactivated_at = null;
                    $user->save();

                    //Return the user details
                    return [
                        'api_token' =>  $token,
                        'verified' =>  $verified,
                        // "data" => $userResponse
                    ];

                }

                return $this->respondUnAuthenticated("Login not successful");

            }
            else {
                return $this->respondUnAuthenticated("Incorrect Credentials");
            }

        }
        return $this->respondError("Accessing an invalid account");
    }

    /**
     * Check User login api
     *
     * This function retrieves the authenticated user's data and includes their associated business data.
     *
     * @param Request $request The request object containing the authenticated user's details
     * @return \Illuminate\Http\JsonResponse The API response containing the user's data, including their business details
     *
     * @throws \Illuminate\Auth\AuthenticationException If the user is not authenticated
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the associated business data is not found
     */
    public function userLoggedIn(Request $request){
        // Retrieve the authenticated user

        if(!$request->user()) {
            return $this->respondError('expired');
        }

        $user = $request->user();

        // Load the associated business data for the user
        $user->load('business');

        // Return the user data, including their business details, using the UserResource transformer
        return $this->respondWithSuccess(UserResource::make($user));
    }

    /**
     * This function handles the password reset process.
     * It generates a random token, saves it to the database, and sends a notification to the user's email.
     *
     * @param Request $request The request object containing the user's email.
     * @return \Illuminate\Http\JsonResponse The API response indicating success or failure.
     */
    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {

        // Check if the user exists and get the user
        $user = User::where('email','=',$request->email)->first();

        // If the user exists
        if (isset($user)) {

            // Generate a random token
            $code = strtoupper(Str::random(env('APP_CODE_GENERATION_LIMIT')));

            // Calculate the token expiration time
            $expires_at = now()->addMinutes(10);

            // Save the password reset code to the database
            DB::table('password_reset_tokens')
                ->updateOrInsert(
                    ['email' => $request->email],
                    ['token' => $code,'expires_at'=> $expires_at,'created_at' => now()]
                );

            // Send a password reset code notification to the user's email
            $user->notify(new PasswordResetNotification($code,date('h:i a', strtotime($expires_at))));

            // Return a success response
            return $this->respondWithSuccess([
            'result' => true,
            ]);

        } else {
            // If the user does not exist, return an error response
            return $this->respondError('Email address does not exist');
        }
    }

    /**
     * This function handles the password reset confirmation process.
     * It validates the password, checks the token, and updates the user's password.
     *
     * @param Request $request The request object containing the user's email, password, and confirmation code.
     * @return \Illuminate\Http\JsonResponse The API response indicating success or failure.
     */
    public function resetPasswordConfirmation(Request $request) {

        // Check if email is valid
        $user = User::where('email','=',$request->email)->first();

        // Validate the password and password confirmation fields
        $validator = Validator::make($request->only(['password','password_confirmation']), [
            'password' => 'bail|required|string|min:5|confirmed',
            'password_confirmation' => 'bail|required|string',
        ],[
            'password.min' => 'Password must be more than 5 characters',
            'password.confirmed' => 'Passwords do not match',
            'password_confirmation.required' => 'Confirm Password is required',
        ]);

        // If the validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        // If the user exists, proceed with the password reset confirmation
        if (isset($user)) {

            // Check if the token exists and is not expired
            $token_exist = DB::table('password_reset_tokens')
            ->where('email','=',$user->email)
            ->where('token','=',$request->code)
            // ->whereDate('expires_at','>',now())
            ->first();

            // If the token does not exist, return an error response
            if(!$token_exist){
                return $this->respondError('Invalid confirmation code.');
            }

            // If the token is expired, return an error response
            if(strtotime($token_exist->expires_at) < strtotime(now())) {
                return $this->respondError('Expired confirmation code.');
            }

            // Update the user's password
            $user->password = Hash::make($request->password);
            $user->save();

            // Send a password reset confirmation notification
            $user->notify(new ConfirmPasswordResetNotification());

            // Return a success response
            return $this->respondWithSuccess([
            'result' => true,
            //    'message' => 'Password has been updated successfully'
            ]);

        } else {
            // If the user does not exist, return an error response
            return $this->respondError('Email address does not exist');
        }
    }
}
