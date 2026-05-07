<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Jobs\SendEmailVerification;
use App\Jobs\SendLoginAlertEmail;
use App\Jobs\SendPasswordChangedEmail;
use App\Jobs\SendProfileUpdatedEmail;
use App\Models\AccessToken;
use App\Models\PortalNotification;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        SendEmailVerification::dispatch($user);
        $this->createPortalNotification(
            $user,
            'Account created',
            'Your account was created successfully. Please verify your email address to keep your account secure.'
        );

        return $this->success($this->authPayload($user), 'Registration successful.', 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $this->createPortalNotification(
            $user,
            'New login',
            'A new login to your account was detected.'
        );
        SendLoginAlertEmail::dispatch($user);

        return $this->success($this->authPayload($user), 'Login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $plainToken = $request->bearerToken();

        if ($plainToken) {
            AccessToken::where('token', hash('sha256', $plainToken))->delete();
        }

        return $this->success(null, 'Logged out.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success((new UserResource($request->user()))->resolve($request), 'Authenticated user fetched.');
    }

    public function verifyToken(Request $request): JsonResponse
    {
        return $this->success([
            'token_valid' => true,
            'user_exists' => true,
            'user' => (new UserResource($request->user()))->resolve($request),
        ], 'Token is valid and user exists.');
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $emailChanged = array_key_exists('email', $validated) && $validated['email'] !== $user->email;
        $profileChanged = $emailChanged || (array_key_exists('name', $validated) && $validated['name'] !== $user->name);
        $passwordChanged = array_key_exists('password', $validated);

        $user->fill($validated);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($profileChanged) {
            SendProfileUpdatedEmail::dispatch($user);
        }

        if ($passwordChanged) {
            SendPasswordChangedEmail::dispatch($user);
        }

        if ($emailChanged) {
            SendEmailVerification::dispatch($user);
        }

        return $this->success((new UserResource($user->refresh()))->resolve($request), 'Profile updated.');
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $user = User::findOrFail($request->route('id'));

        abort_unless(hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification())), 403);

        if (! $user->hasVerifiedEmail() && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->success((new UserResource($user->refresh()))->resolve($request), 'Email verified.');
    }

    public function resendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->success(null, 'Email is already verified.');
        }

        SendEmailVerification::dispatch($user);

        return $this->success(null, 'Verification email sent.');
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

    private function createPortalNotification(User $user, string $title, string $message): void
    {
        PortalNotification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
        ]);
    }
}
