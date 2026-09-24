<?php

namespace Tests\Lessons;

use App\DeviceCheck;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_handles_unknown_fresh_and_expired_heartbeats(): void
    {
        $this->freezeTime();
        $this->seed();
        $device = Device::firstOrFail();
        $check = new DeviceCheck;
        $check->run($device->id);
        $this->assertSame('unknown', $device->fresh()->status);
        $this->assertSame(now()->toDateTimeString(), $device->fresh()->last_checked_at->toDateTimeString());
        $this->artisan('device:heartbeat')->assertSuccessful();
        $check->run($device->id);
        $this->assertSame('online', $device->fresh()->status);
        $this->travel(30)->seconds();
        $check->run($device->id);
        $this->assertSame('offline', $device->fresh()->status);
    }
}
