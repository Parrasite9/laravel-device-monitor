# Laravel Device Communication Monitor

A small SCADA-inspired learning project: simulate PLC/RTU heartbeats, queue communication checks, and watch devices go offline and recover. Laravel 13, PHP 8.4+, SQLite, database queues, and a Blade dashboard. No Redis, Node build, PLC hardware, or external service needed.

This is a local simulation, not a production SCADA system or safety controller. There is no authentication or real device protocol integration. Bind the development server to localhost.

## Setup

Install PHP with SQLite support and Composer, then:

```bash
git clone https://github.com/Parrasite9/laravel-device-monitor.git
cd laravel-device-monitor
composer setup
```

The setup script installs dependencies, creates `.env` and a local SQLite database, runs migrations, and seeds three devices. `.env` and database files are ignored by Git. The default `QUEUE_CONNECTION=database` matters: `sync` runs jobs inline and defeats this exercise.

Run these in four separate terminals from the project directory:

```bash
# 1. Dashboard: http://127.0.0.1:8000
php artisan serve --host=127.0.0.1
```

```bash
# 2. Execute pending jobs
php artisan queue:work --tries=3 --timeout=10
```

```bash
# 3. Dispatch checks every ten seconds
php artisan schedule:work
```

```bash
# 4. Simulate a heartbeat every five seconds from device 1
php artisan devices:simulate 1
```

Refresh the dashboard to see changes. Device IDs are shown in the table. Ctrl+C stops each process.

## Your first exercise

1. Start the four processes above. Refresh: Tank PLC becomes online.
2. Stop only the simulator with Ctrl+C. Leave the worker and scheduler running.
3. Wait 30–40 seconds, then refresh: Tank PLC becomes offline. Worker backlog can increase detection latency.
4. Restart the simulator. Refresh: it is online again, with a recovery transition in history.
5. Stop the queue worker while leaving the scheduler running. Refresh and watch pending jobs accumulate. Device check timestamps stop advancing.
6. Restart the worker. It drains the backlog using current heartbeat facts, not old snapshots.

Other devices go offline after their initial 30-second grace period unless you also simulate their heartbeats. An offline state means no heartbeat was received in time; it does not prove why communication stopped.

## How it works

```text
Simulator → save last_seen_at → online transition
Scheduler → devices:check → database jobs table → queue worker
                                                   ↓
                                      CheckDeviceCommunication
                                                   ↓
                                      latest heartbeat → state + event
```

The scheduler decides **when to enqueue**. The worker **executes the job**. A job contains only the device ID. Heartbeats update synchronously so queued checks see the latest receipt time. Checks and heartbeats serialize database writes inside transactions; a state change and its event commit together. Repeated checks do not create repeated offline events. A never-seen device stays unknown during its grace period.

The queue job allows three attempts with 2- and 5-second retry backoffs and a 10-second timeout. Schedule overlap prevention protects dispatch, not a complete worker backlog. This intentionally small project enqueues a check per device every ten seconds; production monitoring would also need bounded backlog, retention, authentication, and monitor-health visibility.

## Useful commands

```bash
php artisan devices:heartbeat 1             # One heartbeat
php artisan devices:check                   # Queue checks once
php artisan queue:work --stop-when-empty    # Drain the queue, then exit
php artisan schedule:list                  # Inspect the schedule
php artisan queue:failed                   # Inspect exhausted jobs
php artisan queue:retry JOB_UUID           # Retry a failed job
vendor/bin/phpunit                         # Behavioral tests
vendor/bin/pint --test                     # Formatting check
```

## Learn retries deliberately

Temporarily add `throw new \RuntimeException('Practice failure');` at the start of the job's `handle()` method. Restart the worker (workers keep loaded code), dispatch checks, and observe retries and eventual failed jobs. Remove the exception, restart the worker, and retry one failed job by UUID. Do this only in your local learning copy.

## Suggested reading order

1. `routes/console.php` — simulator, dispatch command, scheduler.
2. `app/Jobs/CheckDeviceCommunication.php` — queued work and retry settings.
3. `app/Services/DeviceMonitor.php` — current-state checks and atomic transitions.
4. `tests/Feature/DeviceMonitorTest.php` — timeout boundary, recovery, duplicates, dispatch, and invalid inputs.
5. `routes/web.php` and `resources/views/dashboard.blade.php` — read-only dashboard.

Next exercises: add per-device timeout editing, a notification job on an offline transition, or a CSV history export. Keep alarm acknowledgement separate from communication recovery.

Official references: [Laravel queues](https://laravel.com/docs/13.x/queues) and [task scheduling](https://laravel.com/docs/13.x/scheduling).

## License

MIT. Based on the MIT-licensed Laravel application skeleton.
