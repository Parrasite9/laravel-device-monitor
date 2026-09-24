# 03 · Store and dispatch jobs

**Edit:** `.env` and the `device:check` command in `routes/console.php`.

Set `QUEUE_CONNECTION=database` in `.env`. It may already be set: inspect it yourself. Open `config/queue.php` and find `default` and `connections.database`. Your environment selects which configured connection Laravel uses. `sync` would run the job immediately in the caller.

The database driver needs a `jobs` table. Read `database/migrations/0001_01_01_000002_create_jobs_table.php`: the payload holds serialized job data, attempts counts reservations, and available/reserved timestamps control eligibility. This migration is supplied; don't generate a duplicate.

Apply configuration/schema:

```bash
php artisan config:clear
php artisan migrate
```

Now replace the lesson 03 exception with a call to `CheckDeviceCommunication::dispatch(...)`, passing `$device->id`.

**Check:**

```bash
vendor/bin/phpunit tests/Lessons/DeviceMonitorTest.php
```

The main test checks that dispatch leaves a pending job and an unchanged device, then starts a worker and checks the result. It uses a separate in-memory test database.

**Observe:** with no worker running, run `php artisan device:check`. Your visualizer should show one waiting job. No result should change yet. Dispatch stores work; it isn't completion.

[Answer](answers/03.md) · [Next: the worker](04-follow-the-worker.md)
