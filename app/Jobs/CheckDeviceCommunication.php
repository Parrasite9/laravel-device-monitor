<?php

namespace App\Jobs;

use App\Models\Device;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckDeviceCommunication implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $deviceId) {}

    public function handle(): void
    {
        // This method runs when the worker picks up the job, not when we dispatch it.
        $device = Device::findOrFail($this->deviceId);

        if ($device->last_seen_at === null) {
            $device->status = 'unknown';
        } elseif ($device->last_seen_at->addSeconds(30)->isPast()) {
            $device->status = 'offline';
        } else {
            $device->status = 'online';
        }

        $device->last_checked_at = now();
        $device->save();
    }
}
