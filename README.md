# Build your first Laravel queue

**You write the code. This starter deliberately leaves the important parts unfinished.**

Start here: **[Lesson 01 — Write the check](lessons/01-write-the-check.md)**.

## Setup once

PHP 8.4+, Composer, SQLite support. In the project folder:

```bash
composer setup
php artisan serve --host=127.0.0.1
```

Open http://127.0.0.1:8000 for the visualizer. Keep the server terminal open; use another terminal for exercises. No npm or Redis required.

Updating an earlier clone? Save your edits, pull the changes, run `composer setup`, and stop old workers/schedulers. Existing local data stays; it may show old results.

## Work through one lesson at a time

| Lesson | What you write |
| --- | --- |
| [01](lessons/01-write-the-check.md) | The actual device-check logic |
| [02](lessons/02-write-the-job.md) | A queued job that calls your code |
| [03](lessons/03-connect-the-queue.md) | Queue configuration and dispatch |
| [04](lessons/04-follow-the-worker.md) | Named queue routing and worker execution |
| [05](lessons/05-fail-and-retry.md) | Retry, backoff, and timeout settings |
| [06](lessons/06-reliable-and-recurring.md) | A recurring schedule and repeat-safe work |

The visualizer, device model, database, and heartbeat simulator are supplied scaffolding. Your code lives in `app/DeviceCheck.php`, `app/Jobs/CheckDeviceCommunication.php`, and `routes/console.php`.

**TODO exceptions are intentional.** Each lesson tells you what to replace and how to check it. `composer test` checks the supplied scaffolding only. Lesson tests are separate and fail until you implement the exercises. Passing scaffolding tests does not mean you finished the course.

Completed code is in [lessons/answers](lessons/answers/README.md). Try the exercise before opening its answer. The prior working demo is preserved at [commit 5a24580](https://github.com/Parrasite9/laravel-device-monitor/tree/5a24580).

This is a local simulated SCADA exercise, not a real control system. Keep the unauthenticated visualizer on localhost. Old demo event-table/timeout columns remain for migration compatibility and are unused.

MIT license. [Laravel queue documentation](https://laravel.com/docs/13.x/queues).
