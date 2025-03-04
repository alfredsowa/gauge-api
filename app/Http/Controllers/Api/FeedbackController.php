<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Feedback;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class FeedbackController extends Controller
{
    use ApiResponseHelpers;
    public function saveFeedback(Request $request)
    {
        $business_id = $request->user()->business_id;
        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        $validator = Validator::make($request->all(), [
            'type' => ['required','string'],
            'feedback' => ['required','string','min:5'],
        ],[
            'type.required' => 'Feedback is required',
            'feedback.required' => 'Feedback note is required',
            'feedback.min' => 'Code should be at least 5 characters',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $feedback = Feedback::create([
            'feedback' => $request->feedback,
            'type' => $request->type,
            'from'  =>  $request->user()->id,
        ]);

        if ($feedback) {
            Log::channel('slack_gauge_feedback')->info("Type: {type}.\nMessage: {feedback}",
                ['type'=>$request->type,'feedback'=>$request->feedback]);

            if ($request->type == 'Issue') {
                $message = 'Your issue will be attended and resolved to shortly.';
            }
            else {
                $message = 'We appreciate your feedback. This will be considered in future releases.';
            }

            return $this->respondWithSuccess([
                'message' => $message,
                'saved' => true
            ]);
        }

        return $this->respondError('Feedback did not save. Please try again');
    }
}
