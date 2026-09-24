<?php

use App\Models\Device;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'visualizer')->name('visualizer');

Route::get('/queue-status', function () {
    $device = Device::where('name', 'Practice PLC')->first();

    return response()->json([
        'device' => $device ? [
            'name' => $device->name,
            'heartbeat' => $device->last_seen_at?->toIso8601String(),
            'checked' => $device->last_checked_at?->toIso8601String(),
            'result' => $device->status,
        ] : null,
        'connection' => config('queue.default'),
        'waiting' => DB::table('jobs')->whereNull('reserved_at')->count(),
        'reserved' => DB::table('jobs')->whereNotNull('reserved_at')->count(),
        'failed' => DB::table('failed_jobs')->count(),
        'jobs' => DB::table('jobs')->orderBy('id')->limit(10)
            ->get(['id', 'attempts', 'reserved_at']),
        'observed_at' => now()->toIso8601String(),
    ])->header('Cache-Control', 'no-store');
})->name('queue.status');
