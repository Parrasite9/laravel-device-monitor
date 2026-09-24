<?php

namespace App\Services;

use App\Models\Device;
use Illuminate\Support\Facades\DB;

class DeviceMonitor
{
    public function heartbeat(int $id): void
    {
        // Write first to serialize SQLite writers; checks use the same transaction discipline.
        DB::transaction(function () use ($id) {
            Device::whereKey($id)->update(['last_seen_at' => now()]);
            $device = Device::findOrFail($id);
            $this->transition($device, 'online');
        }, 3);
    }

    public function check(int $id): void
    {
        DB::transaction(function () use ($id) {
            Device::whereKey($id)->update(['last_checked_at' => now()]);
            $device = Device::find($id);
            if (! $device) {
                return;
            }
            // Read current persisted facts, never a snapshot from dispatch time.
            $reference = $device->last_seen_at ?? $device->created_at;
            $status = $reference->copy()->addSeconds($device->timeout_seconds)->lte(now())
                ? 'offline'
                : ($device->last_seen_at ? 'online' : 'unknown');
            $this->transition($device, $status);
        }, 3);
    }

    private function transition(Device $device, string $status): void
    {
        if ($device->status === $status) {
            return;
        }
        DB::table('device_events')->insert([
            'device_id' => $device->id,
            'from_status' => $device->status,
            'to_status' => $status,
            'created_at' => now(),
        ]);
        $device->status = $status;
        $device->save();
    }
}
