# 01 · Write the work first

**Edit:** `app/DeviceCheck.php`, inside `run()`.

A queue doesn't write your business logic. Start with a normal PHP method that checks our pretend PLC. `$device` is already loaded from the database.

Replace the TODO exception with code that:

1. Sets `$device->status` to `'unknown'` when `last_seen_at` is `null`.
2. Sets it to `'offline'` when that heartbeat is at least 30 seconds old.
3. Otherwise sets it to `'online'`.
4. Sets `$device->last_checked_at` to `now()`, then calls `$device->save()`.

Useful pieces (you arrange them):

```php
if (...) { ... } elseif (...) { ... } else { ... }
$device->last_seen_at->addSeconds(30)->lte(now())
```

`last_seen_at` is an immutable date: `addSeconds()` creates a new date. `lte()` means “less than or equal to.” Compare the heartbeat's expiry with the current time. Only access the date after checking for null.

**Check your code:**

```bash
vendor/bin/phpunit tests/Lessons/DeviceCheckTest.php
```

Then try `php artisan device:heartbeat` followed by `php artisan device:check-now`. The visualizer should update without a worker. This command directly calls your method in the same PHP process.

**Before moving on:** why did this work without a queue?

[Hint/answer](answers/01.md) · [Next: write the job](02-write-the-job.md)
