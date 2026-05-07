<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\ProfileUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Foundation\Queue\Queueable;

#[DeleteWhenMissingModels]
class SendProfileUpdatedEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(public User $user)
    {
        $this->onQueue(QueuePriority::Low->value);
    }

    public function handle(): void
    {
        $this->user->notify(new ProfileUpdated);
    }
}
