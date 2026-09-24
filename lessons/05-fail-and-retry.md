# 05 · Make failure visible

**Edit:** your job class. Add three public integer properties:

- `$tries = 3`: stop after three attempts.
- `$backoff = 2`: wait two seconds between failed attempts.
- `$timeout = 10`: limit execution time per attempt.

Temporarily put `throw new \RuntimeException('Practice failure');` first in `handle()`.

Dispatch one job. Start `php artisan queue:work --stop-when-empty`. Watch failures and retries; then run `php artisan queue:failed`. A future retry stays queued until its delay passes. The visualizer shows queue/failure counts; read the exception in `storage/logs/laravel.log`.

**Repair:** remove your temporary throw, retry the UUID printed by `queue:failed` using `php artisan queue:retry UUID`, then start a fresh worker. The failed count should decrease and the result update. Don't delete unrelated failed jobs.

`backoff` delays a retry after a handled failure. The connection's `retry_after` makes an abandoned reservation available again. They are different. In `config/queue.php`, verify effective `retry_after` exceeds your 10-second timeout; the supplied database default is 90 seconds. Overrides matter. A too-short reservation risks concurrent duplicate execution.

A failure after a database write can cause that write to repeat on retry. That's the next lesson.

[Answer](answers/05.md) · [Next: reliable recurring checks](06-reliable-and-recurring.md)
