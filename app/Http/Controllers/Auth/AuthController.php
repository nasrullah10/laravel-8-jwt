<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use JWTAuth;
use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ];

        $validationResult = $this->validateRequest($request, $rules);
        if ($validationResult) {
            return $validationResult;
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        return $this->successResponse($user, 'User created successfully');
    }

    public function authenticate(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ];

        $validationResult = $this->validateRequest($request, $rules);
        if ($validationResult) {
            return $validationResult;
        }

        try {
            if (!$token = JWTAuth::attempt($request->only('email', 'password'))) {
                return $this->errorResponse('Login credentials are invalid.', Response::HTTP_BAD_REQUEST);
            }
        } catch (JWTException $e) {
            return $this->errorResponse('Could not create token.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->successResponse(['token' => $token], 'Authentication successful');
    }

    public function logout(Request $request)
    {
        $rules = [
            'token' => 'required'
        ];

        $validationResult = $this->validateRequest($request, $rules);
        if ($validationResult) {
            return $validationResult;
        }

        try {
            JWTAuth::invalidate($request->token);

            return $this->successResponse(null, 'User has been logged out');
        } catch (JWTException $exception) {
            return $this->errorResponse('Sorry, user cannot be logged out', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
