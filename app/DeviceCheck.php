<?php

namespace App;

use App\Models\Device;

class DeviceCheck
{
    public function run(int $deviceId): void
    {
        $device = Device::findOrFail($deviceId);

        // TODO lesson 01: set status to unknown, offline, or online.
        // No heartbeat => unknown. At least 30 seconds old => offline. Otherwise => online.
        // Then set last_checked_at to now() and save the device.
        // Replace the exception with your code. Open lessons/01-write-the-check.md.
        throw new \LogicException('Lesson 01: write your check in app/DeviceCheck.php.');
    }
}
