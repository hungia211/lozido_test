<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Repositories\AuthRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    protected $authRepository;

    public function __construct(AuthRepositoryInterface $authRepository)
    {
        $this->authRepository = $authRepository;
    }

    public function register(RegisterRequest $request)
    {
        try {
            $user = $this->authRepository->register($request->validated());

            return response()->json([
                'message' => 'Register successfully',
                'user' => $user,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Register failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function login(LoginRequest $request)
    {
        try {
            $user = $this->authRepository->login($request->email);

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'message' => 'Invalid credentials',
                ], 401);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Login failed',
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    // public function logout()
    // {
    //     try {
    //         auth()->user()->currentAccessToken()->delete();

    //         return response()->json([
    //             'message' => 'Logout successfully',
    //         ]);
    //     } catch (\Throwable $th) {
    //         return response()->json([
    //             'message' => 'Logout failed',
    //             'error' => $th->getMessage(),
    //         ], 500);
    //     }
    // }
}
