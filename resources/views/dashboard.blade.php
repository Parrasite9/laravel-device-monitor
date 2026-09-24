<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Device communication monitor</title>
    <style>
        :root { color-scheme: dark; font-family: system-ui, sans-serif; background: #101820; color: #e5edf3; }
        body { max-width: 1050px; margin: 48px auto; padding: 0 20px; }
        h1 { margin-bottom: 8px; } p, small { color: #b3c1cc; } a { color: #86cfff; }
        table { width: 100%; border-collapse: collapse; margin: 24px 0 40px; }
        th, td { padding: 14px 10px; text-align: left; border-bottom: 1px solid #34414d; }
        th { font-size: .85rem; color: #b3c1cc; } .online { color: #7ee0a4; } .offline { color: #ff9999; }
        .unknown { color: #f2cf77; } .table-wrap { overflow-x: auto; } code { color: #b9dfff; }
    </style>
</head>
<body>
    <small>SCADA LEARNING LAB · SIMULATED DEVICES</small>
    <h1>Device communication monitor</h1>
    <p>Last evaluated state, not a live connectivity guarantee. <a href="/">Refresh dashboard</a></p>
    <p>Queue: <strong>{{ $pending }}</strong> pending / reserved jobs · <strong>{{ $failed }}</strong> failed jobs</p>
    <div class="table-wrap"><table>
        <thead><tr><th>Device</th><th>State</th><th>Last heartbeat (UTC)</th><th>Last check (UTC)</th><th>Timeout</th></tr></thead>
        <tbody>@forelse ($devices as $device)
            <tr><td>{{ $device->id }} · {{ $device->name }}</td><td class="{{ $device->status }}">{{ ucfirst($device->status) }}</td><td>{{ $device->last_seen_at?->toDateTimeString() ?? 'Never received' }}</td><td>{{ $device->last_checked_at?->toDateTimeString() ?? 'Not checked' }}</td><td>{{ $device->timeout_seconds }}s</td></tr>
        @empty<tr><td colspan="5">Run php artisan db:seed to add simulated devices.</td></tr>@endforelse</tbody>
    </table></div>
    <h2>Recent state changes</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Time (UTC)</th><th>Device</th><th>Transition</th></tr></thead>
        <tbody>@forelse ($events as $event)
            <tr><td>{{ $event->created_at }}</td><td>{{ $event->name }}</td><td>{{ ucfirst($event->from_status) }} → {{ ucfirst($event->to_status) }}</td></tr>
        @empty<tr><td colspan="3">No state changes yet.</td></tr>@endforelse</tbody>
    </table></div>
    <p>Try <code>php artisan devices:simulate 1</code>. Stop it, wait 30–40 seconds with the scheduler and worker running, then refresh.</p>
</body>
</html>
