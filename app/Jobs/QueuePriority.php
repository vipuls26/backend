<?php

namespace App\Jobs;

enum QueuePriority: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
}
