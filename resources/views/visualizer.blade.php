<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Device queue lab</title>
<style>
:root{color-scheme:dark;font-family:system-ui,sans-serif;background:#101820;color:#edf4f8}*{box-sizing:border-box}body{max-width:1050px;margin:0 auto;padding:40px 24px}h1{font-size:clamp(2rem,5vw,3rem);margin:8px 0}h2{font-size:1.2rem}p{line-height:1.6}small,.muted{color:#b2c3d0}header,section{border-bottom:1px solid #3a4c5a;padding-bottom:24px;margin-bottom:24px}.flow{display:grid;grid-template-columns:1fr auto 1fr auto 1fr;gap:24px;align-items:center;margin:36px 0}.metric{font-size:2.2rem;font-weight:750;margin:12px 0}.arrow{color:#a7dcff;font-size:2rem}.online{color:#86e6ae}.offline{color:#ffaaa4}.unknown,#notice{color:#f1d98c}code{display:block;background:#1b2a36;padding:12px;overflow-x:auto;color:#a7dcff}table{width:100%;border-collapse:collapse}th,td{padding:12px 4px;border-bottom:1px solid #3a4c5a;text-align:left}button{font:inherit;background:#a7dcff;color:#101820;border:0;padding:12px;cursor:pointer}button:focus-visible{outline:3px solid #f1d98c;outline-offset:4px}[hidden]{display:none!important}@media(max-width:680px){.flow{grid-template-columns:1fr;gap:12px}.arrow{transform:rotate(90deg);justify-self:start}}
</style>
</head>
<body>
<header><small>DEVICE MONITOR / QUEUE LAB</small><h1>Watch your queue work.</h1><p>Send a heartbeat. Queue a check. Start a worker.</p><p id="connection" role="status">Connecting to your local app…</p><button id="toggle" type="button">Pause live updates</button><noscript><p>Enable JavaScript, or use php artisan device:status in your terminal.</p></noscript></header>
<p id="notice" role="status" hidden></p>
<main>
<div class="flow">
<div><small>01 / DEVICE</small><h2>Practice PLC</h2><div class="metric" id="age">—</div><p class="muted">Since last heartbeat</p><small id="heartbeat">Waiting for data</small></div>
<span class="arrow" aria-hidden="true">→</span>
<div><small>02 / DATABASE QUEUE</small><h2>Checks waiting</h2><div class="metric" id="waiting">—</div><p><span id="reserved">—</span> reserved · <span id="failed">—</span> failed</p><small>Work waits here for a worker.</small></div>
<span class="arrow" aria-hidden="true">→</span>
<div><small>03 / SAVED RESULT</small><h2>Last device check</h2><div class="metric" id="result">—</div><p id="checked">Waiting for data</p><small>A saved observation, not a live connection test.</small></div>
</div>
<section><h2>Try it in your terminal</h2><p>Leave this page open. Run these from the project folder.</p>
<p><strong>1.</strong> Pretend the PLC just said “I'm here.”</p><code>php artisan device:heartbeat</code>
<p><strong>2.</strong> Add one check. With no worker running, the waiting count goes up.</p><code>php artisan device:check</code>
<p><strong>3.</strong> Run one check. The count drops and the saved result updates.</p><code>php artisan queue:work --once --tries=1</code>
<p class="muted">Heartbeats older than 30 seconds produce an offline result. Send a fresh heartbeat just before checking to see online. This page only reads data; it never sends heartbeats or processes jobs.</p></section>
<section><h2>Jobs in the database</h2><p class="muted">First 10 jobs across this app's database queues. Reserved means claimed by a worker; it does not prove that worker is still alive.</p>
<table><thead><tr><th>Job ID</th><th>State</th><th>Attempts</th></tr></thead><tbody id="jobs"><tr><td colspan="3">Waiting for data</td></tr></tbody></table>
<p class="muted">Refreshes every second. Fast jobs may finish between updates. An empty queue alone does not prove success: check the result timestamp and failed count. This learning view does not measure throughput or worker health.</p></section>
</main>
<script>
const statusUrl = {{ Illuminate\Support\Js::from(route('queue.status')) }};
const el = id => document.getElementById(id);
const show = (id, value) => { el(id).textContent = value; };
const time = value => value ? new Date(value).toLocaleString() : 'Never';
let paused = false;
let busy = false;
function render(data) {
    show('connection', 'Live · refreshed ' + time(data.observed_at));
    ['waiting', 'reserved', 'failed'].forEach(key => show(key, data[key]));
    const notices = [];
    if (!data.device) notices.push('Practice device missing. Run php artisan migrate --seed.');
    if (data.connection !== 'database') notices.push('Set QUEUE_CONNECTION=database in .env, then run php artisan config:clear.');
    if (data.failed) notices.push('Some jobs failed. Run php artisan queue:failed to inspect them.');
    el('notice').hidden = notices.length === 0;
    show('notice', notices.join(' '));
    const device = data.device;
    show('age', device?.heartbeat ? Math.max(0, Math.floor((Date.parse(data.observed_at) - Date.parse(device.heartbeat)) / 1000)) + 's' : 'No heartbeat');
    show('heartbeat', time(device?.heartbeat));
    show('result', device?.result ?? 'Not set up');
    el('result').className = 'metric ' + (['online','offline','unknown'].includes(device?.result) ? device.result : '');
    show('checked', device?.checked ? 'Checked ' + time(device.checked) : 'The worker has not checked yet.');
    const rows = data.jobs.map(job => {
        const row = document.createElement('tr');
        [job.id, job.reserved_at === null ? 'Waiting' : 'Reserved', job.attempts].forEach(value => {
            const cell = document.createElement('td'); cell.textContent = value; row.append(cell);
        });
        return row;
    });
    if (!rows.length) {
        const row = document.createElement('tr'); const cell = document.createElement('td');
        cell.colSpan = 3; cell.textContent = 'No jobs waiting or reserved.'; row.append(cell); rows.push(row);
    }
    el('jobs').replaceChildren(...rows);
}
async function refresh() {
    if (paused || busy) return;
    busy = true;
    try {
        const response = await fetch(statusUrl, {cache:'no-store', signal:AbortSignal.timeout(5000)});
        if (!response.ok) throw new Error('Refresh failed');
        const data = await response.json();
        if (!paused) render(data);
    } catch (error) {
        if (!paused) show('connection', 'Connection lost · displayed values may be stale. Check the web server and migrations. Retrying…');
    } finally { busy = false; }
}
el('toggle').addEventListener('click', () => {
    paused = !paused;
    show('toggle', paused ? 'Resume live updates' : 'Pause live updates');
    if (paused) show('connection', 'Paused · displayed values are a snapshot.');
    else refresh();
});
setInterval(refresh, 1000);
refresh();
</script>
</body>
</html>
