<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisualizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visualizer_loads_and_reads_without_mutating_device(): void
    {
        $this->seed();
        $this->get('/')->assertOk()->assertSee('Watch your queue work.')->assertSee('Start with lesson 01');
        $this->getJson('/queue-status')->assertOk()->assertJsonPath('device.result', 'unknown');
        $this->assertDatabaseHas('devices', ['last_checked_at' => null]);
        $this->assertDatabaseCount('jobs', 0);
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
