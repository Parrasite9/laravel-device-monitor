<?php

use App\DeviceCheck;
use App\Jobs\CheckDeviceCommunication;
use App\Models\Device;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('device:heartbeat', function () {
    $device = Device::where('name', 'Practice PLC')->firstOrFail();
    $device->last_seen_at = now();
    $device->save();

    $this->info('Heartbeat saved. The queued check has not run yet.');
})->purpose('Pretend our PLC just sent a heartbeat');

Artisan::command('device:check', function () {
    if (config('queue.default') !== 'database') {
        $this->error('Set QUEUE_CONNECTION=database in .env, then run php artisan config:clear.');

        return 1;
    }

    $device = Device::where('name', 'Practice PLC')->firstOrFail();

    // Dispatch means put the job on the queue. It does not perform the check here.
    // TODO lesson 03: dispatch CheckDeviceCommunication with this device's ID.
    throw new LogicException('Lesson 03: write the dispatch line in routes/console.php.');
    $this->info('Check queued. Run php artisan queue:work --once to perform it.');
})->purpose('Put one device check on the database queue');

Artisan::command('device:status', function () {
    $device = Device::where('name', 'Practice PLC')->firstOrFail();

    $this->line('Device: '.$device->name);
    $this->line('Last heartbeat: '.($device->last_seen_at?->toDateTimeString() ?? 'none'));
    $this->line('Last checked: '.($device->last_checked_at?->toDateTimeString() ?? 'never'));
    $this->line('Last check result: '.$device->status);
    $this->line('Jobs waiting or running: '.DB::table('jobs')->count());
})->purpose('Show the saved result and queue size without running a check');

Artisan::command('device:check-now', function () {
    $device = Device::where('name', 'Practice PLC')->firstOrFail();
    (new DeviceCheck)->run($device->id);
    $this->info('Check performed immediately, without a queue.');
})->purpose('Lesson 01: run your check in this process');
