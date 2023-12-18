<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        // We get the User from the DB by their email
        $the_user = User::where('email', $request->email)->first();

        // We checked the provided password against the hashed password in the DB

        if (! $the_user || ! Hash::check($request->password, $the_user->password)) {
            // If the passwords don't match, we return the 422 status code with a validation error
            return response()->json([
                'error' => 'The provided credentials are incorrect',
            ], 422);
        }

        $device = substr($request->userAgent() ?? '', 0, 255);
        // If passwords are ok, we generate and return a personal access token with Laravel Sanctum

        return response()->json([
            'access_token' => $the_user->createToken($device)->plainTextToken,
        ]);
    }
}
