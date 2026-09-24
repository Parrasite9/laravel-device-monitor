<?php

use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard', [
        'devices' => Device::orderBy('id')->get(),
        'events' => DB::table('device_events')->join('devices', 'devices.id', '=', 'device_events.device_id')
            ->select('device_events.*', 'devices.name')->orderByDesc('device_events.id')->limit(20)->get(),
        'pending' => DB::table('jobs')->count(),
        'failed' => DB::table('failed_jobs')->count(),
    ]);
});
