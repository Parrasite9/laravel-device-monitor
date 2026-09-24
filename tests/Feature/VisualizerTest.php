<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisualizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visualizer_observes_queue_without_processing_it(): void
    {
        config(['queue.default' => 'database']);
        $this->freezeTime();
        $this->seed();
        $this->get('/')->assertOk()->assertSee('Watch your queue work.');
        $this->artisan('device:heartbeat')->assertSuccessful();
        $this->artisan('device:check')->assertSuccessful();
        $this->getJson('/queue-status')->assertOk()
            ->assertJsonPath('waiting', 1)->assertJsonPath('reserved', 0)
            ->assertJsonPath('device.result', 'unknown')->assertJsonPath('device.checked', null)
            ->assertJsonCount(1, 'jobs');
        $this->assertDatabaseCount('jobs', 1);
        $this->artisan('queue:work', ['--once' => true, '--tries' => 1])->assertSuccessful();
        $this->getJson('/queue-status')->assertOk()
            ->assertJsonPath('waiting', 0)->assertJsonPath('device.result', 'online')
            ->assertJsonPath('device.checked', now()->toIso8601String());
    }

    public function test_missing_device_and_reserved_jobs_are_reported_honestly(): void
    {
        DB::table('jobs')->insert([
            'queue' => 'default', 'payload' => '{}', 'attempts' => 1,
            'reserved_at' => time(), 'available_at' => time(), 'created_at' => time(),
        ]);
        $this->getJson('/queue-status')->assertOk()->assertJsonPath('device', null)
            ->assertJsonPath('waiting', 0)->assertJsonPath('reserved', 1);
    }
}
