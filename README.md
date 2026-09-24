# Learn Laravel queues: one device, one job

A tiny terminal-only lesson for a junior developer. A pretend PLC sends a heartbeat (a message that says “I'm here”). Your job checks whether that heartbeat is more than 30 seconds old.

**The lesson: putting a job on a queue does not run it. A worker runs it.**

No hardware, dashboard, scheduler, Redis, or JavaScript setup. This is a sequential local exercise, not a real SCADA monitor.

## 1. Set up once

You need PHP 8.4+ with SQLite support, Composer, and Git.

```bash
git clone https://github.com/Parrasite9/laravel-device-monitor.git
cd laravel-device-monitor
composer setup
```

Already cloned the earlier version? Run `git pull --ff-only`, then `composer setup`. Stop any old queue workers, simulators, and scheduler processes with Ctrl+C first. The old demo records remain in your database; this lesson only uses `Practice PLC`.

Setup creates a local SQLite database and one practice device. Keep `QUEUE_CONNECTION=database` in `.env`. You do not need to start a web server.

## 2. See the starting state

Use one terminal for this entire lesson. Do not leave another worker running.

```bash
php artisan device:status
```

On a fresh installation, expect:

```text
Device: Practice PLC
Last heartbeat: none
Last checked: never
Last check result: unknown
Jobs waiting or running: 0
```

`unknown` means we have no heartbeat to evaluate. The result is a saved observation, not a continuously updating connection status.

## 3. Put a check on the queue

```bash
php artisan device:heartbeat
php artisan device:check
php artisan device:status
```

The heartbeat has a timestamp, but **Last checked is still never** and **Jobs waiting or running is 1**. Nothing is broken: there is no worker running yet.

Open `routes/console.php` and find:

```php
CheckDeviceCommunication::dispatch($device->id);
```

`dispatch()` puts a job in the database's `jobs` table. The ID tells the job which device to check.

## 4. Run exactly one job

```bash
php artisan queue:work --once --tries=1
php artisan device:status
```

The worker prints `RUNNING` and `DONE`, then exits. The queue size becomes 0 and Last checked gains a timestamp.

The result is `online` if you ran the job within 30 seconds of the heartbeat. If you took longer while reading, `offline` is correct! To see online, run these together:

```bash
php artisan device:heartbeat
php artisan device:check
php artisan queue:work --once --tries=1
php artisan device:status
```

Open `app/Jobs/CheckDeviceCommunication.php`. Its `handle()` method is the work that the worker executes. `ShouldQueue` tells Laravel this job belongs on a queue.

## 5. Simulate lost communication

Wait at least 31 seconds without sending a heartbeat. Then:

```bash
php artisan device:check
php artisan queue:work --once --tries=1
php artisan device:status
```

Expect `offline`. Send a fresh heartbeat and repeat those three commands to see `online` again.

A missing heartbeat only tells us communication was not observed recently. We are not diagnosing a real PLC failure.

## The three pieces

| Piece | In this lesson | Responsibility |
| --- | --- | --- |
| Dispatch | `php artisan device:check` | Put work on the queue |
| Queue | SQLite `jobs` table | Hold work until a worker takes it |
| Worker | `php artisan queue:work --once --tries=1` | Run one job's `handle()` method |

Read just two files first: `routes/console.php` and `app/Jobs/CheckDeviceCommunication.php`. `app/Models/Device.php` connects PHP to the device row in the database. Everything else can wait.

## If something seems broken

- **Job waiting, result unchanged:** run the worker. Queuing is not completion.
- **Result offline after a fresh heartbeat:** more than 30 seconds may have passed before the worker checked it. Send another heartbeat and check again.
- **Worker waits without exiting:** `--once` can wait when the queue is empty. Press Ctrl+C, dispatch a check, then run it again.
- **Jobs disappear immediately:** stop any other running workers. The lesson needs you to start the worker manually.
- **Queue configuration error:** set `QUEUE_CONNECTION=database` in `.env`, then run `php artisan config:clear`.
- **Missing table or practice device:** run `php artisan migrate --seed`.
- **Worker prints FAIL:** run `php artisan queue:failed` and read the error in `storage/logs/laravel.log`.

## Optional: your first code change

Change the timeout in `handle()` from 30 seconds to 10 seconds. Repeat the outage exercise, waiting 11 seconds. The next `--once` command starts a fresh worker and loads your edited code.

Automated checks: `composer test`. The main test uses a real database queue and worker to prove that dispatching alone does not update the result.

Later lessons can add automatic checks and retries. For now, focus on dispatch → queue → worker → saved result.

Reference: [Laravel queue documentation](https://laravel.com/docs/13.x/queues).

MIT license. Based on the Laravel application skeleton. The original larger demo remains in Git history; its event table and timeout column remain in the migration for existing installations but are unused by this lesson.
