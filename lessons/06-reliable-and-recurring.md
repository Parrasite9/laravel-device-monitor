# 06 · Repeat safely, then automate

**First experiment:** enqueue two checks for the same device and drain them. Your code should update one device, not create two devices. This is repeat-safe for this sequential lab.

Queues can deliver work more than once. Setting a current state is safer to repeat than “increment a counter” or “send an email.” Real notifications need deduplication; concurrent workers and heartbeats need transaction/locking design. This starter is not a concurrent production monitor.

**Write:** import `Illuminate\Support\Facades\Schedule` into `routes/console.php`. Schedule the `device:check` command every minute using `Schedule::command(...)->everyMinute()`.

Run two separate processes:

```bash
php artisan schedule:work
php artisan queue:work
```

The scheduler decides **when to dispatch**. The worker **executes**. Stop the worker and watch jobs accumulate; restart it and watch them drain. Stop both with Ctrl+C when finished. The heartbeat remains manual, so offline results are expected after 30 seconds.

On a server, a process manager (such as Supervisor or systemd) keeps workers running; a scheduler trigger dispatches recurring work. Code deployment needs worker restart handling. Adding workers doesn't make shared writes safe automatically.

**Explain back:** where is a job before dispatch, while waiting, during execution, and after success or final failure?

[Answer + next topics](answers/06.md)
