<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\AccessToken;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        return $this->success($this->authPayload($user), 'Registration successful.', 201);
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $this->success($this->authPayload($user), 'Login successful.');
    }

    public function logout(Request $request)
    {
        $plainToken = $request->bearerToken();

        if ($plainToken) {
            AccessToken::where('token', hash('sha256', $plainToken))->delete();
        }

        return $this->success(null, 'Logged out.');
    }

    public function me(Request $request)
    {
        return $this->success((new UserResource($request->user()))->resolve($request), 'Authenticated user fetched.');
    }

    public function verifyToken(Request $request)
    {
        return $this->success([
            'token_valid' => true,
            'user_exists' => true,
            'user' => (new UserResource($request->user()))->resolve($request),
        ], 'Token is valid and user exists.');
    }

    private function authPayload(User $user): array
    {
        $plainToken = Str::random(80);

        $user->accessTokens()->create([
            'name' => 'api',
            'token' => hash('sha256', $plainToken),
        ]);

        return [
            'token' => $plainToken,
            'user' => (new UserResource($user))->resolve(),
        ];
    }
}
