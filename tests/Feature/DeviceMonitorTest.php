<?php

namespace Tests\Feature;

use App\Jobs\CheckDeviceCommunication;
use App\Models\Device;
use App\Services\DeviceMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DeviceMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_device_has_a_grace_period_then_goes_offline_once(): void
    {
        $device = Device::create(['name' => 'PLC']);
        $monitor = app(DeviceMonitor::class);
        $monitor->check($device->id);
        $this->assertSame('unknown', $device->fresh()->status);
        $this->travel(30)->seconds();
        $job = new CheckDeviceCommunication($device->id);
        $job->handle($monitor);
        $job->handle($monitor);
        $this->assertSame('offline', $device->fresh()->status);
        $this->assertDatabaseCount('device_events', 1);
    }

    public function test_heartbeat_recovers_device_and_old_queued_check_reads_fresh_state(): void
    {
        $device = Device::create(['name' => 'PLC']);
        $monitor = app(DeviceMonitor::class);
        $monitor->heartbeat($device->id);
        $this->travel(31)->seconds();
        $monitor->check($device->id);
        $this->assertSame('offline', $device->fresh()->status);
        $oldJob = new CheckDeviceCommunication($device->id);
        $monitor->heartbeat($device->id);
        $oldJob->handle($monitor);
        $monitor->heartbeat($device->id);
        $this->assertSame('online', $device->fresh()->status);
        $this->assertDatabaseCount('device_events', 3);
    }

    public function test_dispatch_command_queues_one_job_per_device(): void
    {
        Queue::fake();
        $this->seed();
        $this->artisan('devices:check')->assertSuccessful();
        Queue::assertPushed(CheckDeviceCommunication::class, 3);
    }

    public function test_deleted_device_job_is_harmless(): void
    {
        (new CheckDeviceCommunication(999))->handle(app(DeviceMonitor::class));
        $this->assertDatabaseCount('device_events', 0);
    }

    public function test_dashboard_and_heartbeat_command(): void
    {
        $device = Device::create(['name' => 'PLC']);
        $this->artisan('devices:heartbeat', ['device' => $device->id])->assertSuccessful();
        $this->get('/')->assertOk()->assertSee('PLC')->assertSee('Online');
        $this->artisan('devices:heartbeat', ['device' => 999])->assertFailed();
        $this->artisan('devices:simulate', ['device' => $device->id, '--interval' => 0])->assertFailed();
    }
}
