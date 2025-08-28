<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Helpers\ApiResponse;
use App\Models\User;

class AuthController extends Controller
{
  // Register new user
  public function register(Request $request)
  {
      // ✅ All code must be inside the method
      $validator = Validator::make($request->all(), [
          'name'     => 'required|string',
          'email'    => 'required|email|unique:users,email',
          'password' => 'required|min:6',
      ]);

      if ($validator->fails()) {
          return ApiResponse::error('Validation error', 422, $validator->errors());
      }

      $user = User::create([
          'name'     => $request->name,
          'email'    => $request->email,
          'password' => Hash::make($request->password),
      ]);

      return ApiResponse::success($user, 'User registered successfully');
  }

    // Login
    public function login(Request $request) {
        $credentials = $request->only('email', 'password');

        if (! $token = JWTAuth::attempt($credentials)) {
            return ApiResponse::error('Invalid email or password', 401);
        }
        $user = JWTAuth::user();
        return ApiResponse::success([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => JWTAuth::factory()->getTTL() * 60,
            'user'         => [
                        'name'  => $user->name,
                        'email' => $user->email,
                        ],
        ], 'Login successful');
    }

    // Logout
    public function logout() {
        JWTAuth::invalidate(JWTAuth::getToken());
        return ApiResponse::success([], 'Logout successful');
    }

    // Profile (to test token)
    public function profile() {
        return ApiResponse::success(JWTAuth::user(), 'User profile');
    }
}
