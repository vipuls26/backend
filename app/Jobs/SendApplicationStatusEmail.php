<?php

namespace App\Jobs;

use App\Models\Application;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Attributes\DeleteWhenMissingModels;
use Illuminate\Foundation\Queue\Queueable;

#[DeleteWhenMissingModels]
class SendApplicationStatusEmail implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * @var array<int, int>
     */
    public array $backoff = [10, 30, 60];

    public function __construct(public Application $application)
    {
        $this->onQueue($application->status === 'interview_scheduled'
            ? QueuePriority::Medium->value
            : QueuePriority::Low->value
        );
    }

    public function handle(): void
    {
        $this->application->loadMissing(['job.company', 'user']);

        $this->application->user->notify(new ApplicationStatusChanged($this->application));
    }
}
