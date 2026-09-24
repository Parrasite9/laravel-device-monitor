<?php

namespace Tests\Lessons;

use App\Jobs\CheckDeviceCommunication;
use App\Models\Device;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_waits_in_database_until_worker_runs(): void
    {
        config(['queue.default' => 'database']);
        $this->freezeTime();
        $this->seed();
        $this->artisan('device:heartbeat')->assertSuccessful();
        $this->artisan('device:check')->assertSuccessful();

        $this->assertDatabaseCount('jobs', 1);
        $this->assertDatabaseHas('devices', ['status' => 'unknown', 'last_checked_at' => null]);

        $this->artisan('queue:work', ['--once' => true, '--tries' => 1])->assertSuccessful();

        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseHas('devices', ['status' => 'online']);
        $this->artisan('device:status')->expectsOutput('Last check result: online')->assertSuccessful();
    }

    public function test_check_uses_current_heartbeat_and_detects_outage_and_recovery(): void
    {
        $this->freezeTime();
        $this->seed();
        $device = Device::firstOrFail();
        $job = new CheckDeviceCommunication($device->id);
        $job->handle();
        $this->assertSame('unknown', $device->fresh()->status);

        $this->artisan('device:heartbeat')->assertSuccessful();
        $this->travel(31)->seconds();
        $job->handle();
        $this->assertSame('offline', $device->fresh()->status);

        $this->artisan('device:heartbeat')->assertSuccessful();
        $job->handle();
        $this->assertSame('online', $device->fresh()->status);
    }

    public function test_sync_queue_is_rejected_so_the_lesson_cannot_silently_run_inline(): void
    {
        config(['queue.default' => 'sync']);
        $this->artisan('device:check')->assertFailed();
        $this->assertDatabaseCount('jobs', 0);
    }
}
