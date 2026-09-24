<?php

namespace Tests\Lessons;

use App\Jobs\CheckDeviceCommunication;
use App\Models\Device;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_is_queueable_and_executes_the_check(): void
    {
        $this->freezeTime();
        $this->seed();
        $device = Device::firstOrFail();
        $this->artisan('device:heartbeat')->assertSuccessful();
        $job = new CheckDeviceCommunication($device->id);
        $this->assertInstanceOf(ShouldQueue::class, $job);
        $job->handle();
        $this->assertSame('online', $device->fresh()->status);
    }
}
