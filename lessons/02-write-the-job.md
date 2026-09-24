# 02 · Wrap your work in a job

**Edit:** `app/Jobs/CheckDeviceCommunication.php`.

A job is a PHP object representing work that can be performed later. Laravel normally generates its shell with `php artisan make:job JobName`; this starter already supplies the shell so you can focus on its parts.

Make two edits:

1. Add `implements ShouldQueue` to the class declaration. This tells Laravel to enqueue ordinary dispatches instead of running them inline.
2. Replace the TODO exception in `handle()` with a call to `run($this->deviceId)` on a new `DeviceCheck` object.

The imports are already provided. For object creation, the PHP pattern is `(new ClassName)->method(arguments)`.

Understand the pieces:

- `__construct()` stores the device ID when you create the job.
- `Queueable` is a trait supplying dispatch and queue helpers.
- `handle()` contains what the worker executes later.
- We carry an ID, not a database connection or request object. Your check reads the latest heartbeat at execution time.

**Check:**

```bash
vendor/bin/phpunit tests/Lessons/JobTest.php
```

These tests call the job directly and check that it is queueable. They do not start a worker yet.

**Explain it:** which method runs when a worker processes the job?

[Answer](answers/02.md) · [Next: connect the queue](03-connect-the-queue.md)
