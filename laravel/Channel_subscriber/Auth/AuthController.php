<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequestClient;
use App\Http\Requests\Auth\RegisterRequestClient;
use App\Modules\InterFaces\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function register(RegisterRequestClient $request)
    {
        $user = $this->userRepository->create($request->validated());
        return response()->json(['message' => 'User registered successfully', 'user' => $user], 201);
    }

    public function login(LoginRequestClient $request)
    {
        $user = $this->userRepository->model()->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['message' => 'User logged in successfully', 'user' => $user, 'token' => $token], 201);
    }

}
