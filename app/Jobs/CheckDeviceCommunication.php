<?php

namespace App\Jobs;

use App\DeviceCheck;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

// TODO lesson 02: make this class implement ShouldQueue.
class CheckDeviceCommunication
{
    use Queueable;

    // Store only the ID: the worker will read the current device later.
    public function __construct(public int $deviceId) {}

    public function handle(): void
    {
        // TODO lesson 02: call DeviceCheck::run through a new DeviceCheck instance.
        throw new \LogicException('Lesson 02: write the job in app/Jobs/CheckDeviceCommunication.php.');
    }
}
