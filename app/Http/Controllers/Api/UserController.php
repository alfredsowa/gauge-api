<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserListResource;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponseHelpers;

    //Get the list of users
    public function users(Request $request) {
        return UserListResource::collection(User::with('business')->paginate(50));
    }

    /**
     * Get a single user by its ID.
     *
     * @param int $id The ID of the user to retrieve.
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\JsonResource
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If the user with the given ID does not exist.
     */
    public function user($id) {

        // Check if the user exists
        if ($user = User::find($id)) {
            // If the user exists, return it as a JSON resource
            return new UserResource($user->load('business'));
        }

        // If the user does not exist, return a 204 No Content response
        return $this->respondNoContent(null);
    }
}
