<?php

use App\Jobs\CheckDeviceCommunication;
use App\Models\Device;
use App\Services\DeviceMonitor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('devices:check', function () {
    Device::query()->eachById(fn (Device $device) => CheckDeviceCommunication::dispatch($device->id));
    $this->info('Checks queued. Run queue:work to process them.');
})->purpose('Queue a communication check for every device');

Artisan::command('devices:heartbeat {device : Device ID}', function () {
    $device = Device::find($this->argument('device'));
    if (! $device) {
        $this->error('Device not found.');

        return 1;
    }
    app(DeviceMonitor::class)->heartbeat($device->id);
    $this->info("Heartbeat received from {$device->name}.");
})->purpose('Record one simulated heartbeat');

Artisan::command('devices:simulate {device : Device ID} {--interval=5 : Seconds between heartbeats}', function () {
    $device = Device::find($this->argument('device'));
    $interval = filter_var($this->option('interval'), FILTER_VALIDATE_INT);
    if (! $device || $interval === false || $interval < 1 || $interval > 60) {
        $this->error('Use an existing device ID and an interval between 1 and 60 seconds.');

        return 1;
    }
    $this->info("Simulating {$device->name}. Ctrl+C stops heartbeats.");
    while (true) {
        app(DeviceMonitor::class)->heartbeat($device->id);
        $this->line(now()->toTimeString().' heartbeat received');
        sleep($interval);
    }
})->purpose('Simulate a device until interrupted');

Schedule::command('devices:check')->everyTenSeconds()->withoutOverlapping();
