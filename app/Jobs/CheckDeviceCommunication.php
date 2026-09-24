<?php

namespace App\Jobs;

use App\Services\DeviceMonitor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckDeviceCommunication implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 10;

    public function __construct(public int $deviceId) {}

    public function backoff(): array
    {
        return [2, 5];
    }

    public function handle(DeviceMonitor $monitor): void
    {
        $monitor->check($this->deviceId);
    }
}
