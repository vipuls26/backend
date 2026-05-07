<?php

namespace Tests\Feature;

use App\Jobs\SendEmailVerification;
use App\Jobs\SendLoginAlertEmail;
use App\Jobs\SendPasswordChangedEmail;
use App\Jobs\SendProfileUpdatedEmail;
use App\Models\User;
use App\Notifications\PasswordChanged;
use App\Notifications\ProfileUpdated;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AuthProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_queues_email_verification_and_creates_notification(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Taylor Otwell',
            'email' => 'taylor@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'candidate',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.user.email_verified_at', null);

        $user = User::where('email', 'taylor@example.com')->firstOrFail();

        Queue::assertPushedOn('low', SendEmailVerification::class);
        Queue::assertPushed(SendEmailVerification::class, fn (SendEmailVerification $job): bool => $job->user->is($user));
        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'Account created',
        ]);
    }

    public function test_login_creates_new_login_notification(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'email' => 'candidate@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'candidate@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'title' => 'New login',
        ]);
        Queue::assertPushedOn('high', SendLoginAlertEmail::class);
        Queue::assertPushed(SendLoginAlertEmail::class, fn (SendLoginAlertEmail $job): bool => $job->user->is($user));
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'email_verified_at' => now(),
        ]);
        $token = $this->plainTokenFor($user);

        $response = $this
            ->withToken($token)
            ->patchJson('/api/profile', [
                'name' => 'New Name',
                'email' => 'new@example.com',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.email', 'new@example.com')
            ->assertJsonPath('data.email_verified_at', null);

        $user->refresh();

        $this->assertTrue(Hash::check('new-password', $user->password));
        Queue::assertPushedOn('low', SendProfileUpdatedEmail::class);
        Queue::assertPushed(SendProfileUpdatedEmail::class, fn (SendProfileUpdatedEmail $job): bool => $job->user->is($user));
        Queue::assertPushedOn('high', SendPasswordChangedEmail::class);
        Queue::assertPushed(SendPasswordChangedEmail::class, fn (SendPasswordChangedEmail $job): bool => $job->user->is($user));
        Queue::assertPushedOn('low', SendEmailVerification::class);
        Queue::assertPushed(SendEmailVerification::class, fn (SendEmailVerification $job): bool => $job->user->is($user));
    }

    public function test_password_only_profile_update_queues_password_changed_email(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $token = $this->plainTokenFor($user);

        $this
            ->withToken($token)
            ->patchJson('/api/profile', [
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertOk();

        Queue::assertPushedOn('high', SendPasswordChangedEmail::class);
        Queue::assertPushed(SendPasswordChangedEmail::class, fn (SendPasswordChangedEmail $job): bool => $job->user->is($user));
        Queue::assertNotPushed(SendProfileUpdatedEmail::class);
        Queue::assertNotPushed(SendEmailVerification::class);
    }

    public function test_email_verification_job_mails_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        (new SendEmailVerification($user))->handle();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_profile_updated_job_mails_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        (new SendProfileUpdatedEmail($user))->handle();

        Notification::assertSentTo($user, ProfileUpdated::class);
    }

    public function test_password_changed_job_mails_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        (new SendPasswordChangedEmail($user))->handle();

        Notification::assertSentTo($user, PasswordChanged::class);
    }

    public function test_verification_link_marks_email_as_verified(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $notification) use ($user): bool {
            $response = $this->getJson($notification->toMail($user)->actionUrl);

            $response
                ->assertOk()
                ->assertJsonPath('data.email_verified_at', fn (?string $verifiedAt): bool => $verifiedAt !== null);

            return true;
        });
    }

    private function plainTokenFor(User $user): string
    {
        $plainToken = 'plain-test-token';

        $user->accessTokens()->create([
            'name' => 'api',
            'token' => hash('sha256', $plainToken),
        ]);

        return $plainToken;
    }
}
