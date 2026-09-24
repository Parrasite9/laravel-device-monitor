# 04 · Follow the worker

**Write:** append `->onQueue('monitoring')` to your dispatch call.

A **connection** selects the backend (database here). A **queue** is a named lane inside that backend. Jobs in `monitoring` will wait if your worker only watches `default`.

Dispatch a new check, then run:

```bash
php artisan queue:work database --queue=monitoring --once --tries=1
```

The worker loads Laravel, claims one available job, reconstructs it, calls `handle()`, and removes it after success. `--once` exits after one job, but can wait when there are none. Ctrl+C stops it.

Watch the visualizer: waiting decreases, result timestamp changes. Jobs may finish too quickly to see the reserved state between one-second refreshes.

**Write a comment** above dispatch explaining why this worker needs `--queue=monitoring`.

Experiment: dispatch into `monitoring`, then run a worker with `--queue=default --stop-when-empty`. Why does the monitoring job remain? Older default jobs may still be processed.

Restore dispatch to the default queue before the next lesson. The supplied lesson tests expect default routing.

A long-running `queue:work` process keeps loaded code. Restart it after code edits. Our `--once` commands start fresh each time.

[Answer](answers/04.md) · [Next: failures](05-fail-and-retry.md)
