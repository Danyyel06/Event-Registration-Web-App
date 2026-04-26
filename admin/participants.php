<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$selected_event_id = (int)($_GET['event_id'] ?? 0);
$all_events = $pdo->query('SELECT id, title, date FROM events ORDER BY date DESC')->fetchAll();

$event = null;
$students = [];

if ($selected_event_id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM events WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $selected_event_id]);
    $event = $stmt->fetch();

    if ($event) {
        $stmt = $pdo->prepare('SELECT student_name, matric_no, email, reg_code, registered_at FROM registrations WHERE event_id = :event_id ORDER BY registered_at ASC');
        $stmt->execute([':event_id' => $selected_event_id]);
        $students = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Participants – Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root { --black:#0a0a0a;--ink:#1a1a1a;--ink-soft:#525252;--ink-muted:#737373;--ink-faint:#a3a3a3;--border:#e5e5e5;--border-light:#f0f0f0;--bg:#fafafa;--surface:#ffffff;--hover:#f5f5f5;--font:'Plus Jakarta Sans',sans-serif; }
        body { font-family:var(--font);background:var(--bg);color:var(--ink);min-height:100vh;-webkit-font-smoothing:antialiased; }

        nav { background:var(--surface);border-bottom:1px solid var(--border);position:sticky;top:0;z-index:100; }
        .nav-inner { max-width:1100px;margin:0 auto;padding:0 2rem;height:56px;display:flex;align-items:center;justify-content:space-between; }
        .nav-brand { display:flex;align-items:center;gap:8px;text-decoration:none; }
        .brand-mark { width:28px;height:28px;background:var(--black);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:14px; }
        .brand-name { font-size:.875rem;font-weight:700;color:var(--ink); }
        .brand-tag  { font-size:.68rem;color:var(--ink-faint);margin-left:2px; }
        .nav-links { display:flex;align-items:center;gap:.125rem; }
        .nav-links a { font-size:.8rem;color:var(--ink-muted);text-decoration:none;padding:6px 10px;border-radius:6px;font-weight:500;transition:color .15s,background .15s; }
        .nav-links a:hover { color:var(--ink);background:var(--hover); }
        .nav-links a.active { color:var(--ink);font-weight:600; }
        .nav-links a.logout { border:1px solid var(--border);color:var(--ink);font-weight:600;margin-left:.5rem; }

        .wrap { max-width:1100px;margin:0 auto;padding:2.5rem 2rem 5rem; }

        .page-header { margin-bottom:2rem;padding-bottom:1.5rem;border-bottom:1px solid var(--border); }
        .page-eyebrow { font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--ink-faint);margin-bottom:.4rem; }
        .page-title { font-size:1.75rem;font-weight:800;color:var(--black);letter-spacing:-.03em; }

        /* Selector */
        .selector-card { background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:1rem;flex-wrap:wrap; }
        .selector-label { font-size:.75rem;font-weight:700;color:var(--ink-muted);text-transform:uppercase;letter-spacing:.08em;white-space:nowrap; }
        .selector-card select { flex:1;min-width:200px;padding:.625rem .875rem;border:1px solid var(--border);border-radius:6px;font-size:.875rem;font-family:var(--font);color:var(--ink);background:var(--surface);outline:none;transition:border-color .15s;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%23737373'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:16px;padding-right:2.25rem; }
        .selector-card select:focus { border-color:var(--black);box-shadow:0 0 0 2px rgba(10,10,10,.08); }

        /* Event banner */
        .event-banner { background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.75rem; }
        .banner-title { font-size:.9rem;font-weight:700;color:var(--black);margin-bottom:.3rem; }
        .banner-meta { display:flex;gap:1rem;flex-wrap:wrap; }
        .banner-meta span { font-size:.75rem;color:var(--ink-muted);display:flex;align-items:center;gap:4px; }
        .attendance-pill { background:#f5f5f5;border:1px solid var(--border);color:var(--ink);border-radius:999px;padding:5px 14px;font-size:.78rem;font-weight:700;white-space:nowrap; }

        .print-btn { display:inline-flex;align-items:center;gap:6px;padding:6px 14px;background:var(--surface);color:var(--ink);border:1px solid var(--border);border-radius:6px;font-size:.78rem;font-family:var(--font);font-weight:600;cursor:pointer;transition:background .15s;margin-bottom:1rem; }
        .print-btn:hover { background:var(--hover); }

        /* Table */
        .table-card { background:var(--surface);border:1px solid var(--border);border-radius:10px;overflow:hidden; }
        .table-scroll { overflow-x:auto; }
        table { width:100%;border-collapse:collapse;min-width:680px; }
        thead tr { border-bottom:1px solid var(--border-light);background:#fafafa; }
        th { padding:.75rem 1.25rem;text-align:left;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--ink-faint); }
        td { padding:.875rem 1.25rem;font-size:.83rem;color:var(--ink);border-bottom:1px solid var(--border-light);vertical-align:middle; }
        tbody tr:last-child td { border-bottom:none; }
        tbody tr:hover { background:var(--hover); }
        .td-sn { color:var(--ink-faint);font-size:.75rem; }
        .td-name { font-weight:600;color:var(--black); }
        .td-muted { color:var(--ink-muted);font-size:.78rem; }
        .reg-code { font-family:'SF Mono','Fira Code',monospace;font-size:.75rem;font-weight:700;background:#f5f5f5;color:var(--ink);border:1px solid var(--border);padding:2px 8px;border-radius:4px;letter-spacing:.06em; }

        .empty-state { text-align:center;padding:4rem 2rem;color:var(--ink-faint);font-size:.83rem; }
        .prompt-state { text-align:center;padding:5rem 2rem;background:var(--surface);border:1px solid var(--border);border-radius:10px; }
        .prompt-state h3 { font-size:1rem;font-weight:700;color:var(--ink);margin-bottom:.4rem; }
        .prompt-state p { font-size:.83rem;color:var(--ink-faint); }

        @media print {
            nav,.selector-card,.print-btn { display:none!important; }
            body { background:white; }
            .wrap { max-width:100%;margin:0;padding:0; }
        }

        @media(max-width:640px) { .wrap{padding:1.75rem 1.25rem 4rem} .nav-inner{padding:0 1.25rem} }

        @keyframes fadeUp { from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
        .wrap { animation:fadeUp .3s ease both; }
    </style>
</head>
<body>

<nav>
    <div class="nav-inner">
        <a href="dashboard.php" class="nav-brand">
            <div class="brand-mark">🎓</div>
            <span class="brand-name">Faculty Events</span>
            <span class="brand-tag">Admin</span>
        </a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="events.php">Events</a>
            <a href="participants.php" class="active">Participants</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
</nav>

<div class="wrap">
    <div class="page-header">
        <p class="page-eyebrow">Admin Panel</p>
        <h1 class="page-title">Participants</h1>
    </div>

    <div class="selector-card">
        <span class="selector-label">Event</span>
        <form method="get" action="" style="flex:1;display:flex;align-items:center;gap:.75rem">
            <select name="event_id" onchange="this.form.submit()">
                <option value="">Select an event to view participants</option>
                <?php foreach ($all_events as $ev): ?>
                    <option value="<?= $ev['id'] ?>" <?= ($selected_event_id == $ev['id'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($ev['title']) ?> (<?= date('d M Y', strtotime($ev['date'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($event): ?>
        <div class="event-banner">
            <div>
                <div class="banner-title"><?= htmlspecialchars($event['title']) ?></div>
                <div class="banner-meta">
                    <span><?= date('d F Y', strtotime($event['date'])) ?></span>
                    <span><?= htmlspecialchars($event['venue']) ?></span>
                </div>
            </div>
            <div class="attendance-pill"><?= count($students) ?> / <?= $event['capacity'] ?> registered</div>
        </div>

        <?php if (empty($students)): ?>
            <div class="empty-state">No students have registered for this event yet.</div>
        <?php else: ?>
            <button class="print-btn" onclick="window.print()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print / Save PDF
            </button>

            <div class="table-card">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Matric No.</th>
                                <th>Email</th>
                                <th>Reg. Code</th>
                                <th>Registered At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $i => $s): ?>
                                <tr>
                                    <td class="td-sn"><?= $i + 1 ?></td>
                                    <td class="td-name"><?= htmlspecialchars($s['student_name']) ?></td>
                                    <td class="td-muted"><?= htmlspecialchars($s['matric_no']) ?></td>
                                    <td class="td-muted"><?= htmlspecialchars($s['email']) ?></td>
                                    <td><span class="reg-code"><?= htmlspecialchars($s['reg_code']) ?></span></td>
                                    <td class="td-muted"><?= date('d M Y, g:ia', strtotime($s['registered_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($selected_event_id === 0): ?>
        <div class="prompt-state">
            <h3>Select an event above</h3>
            <p>Choose an event from the dropdown to view its participant list.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>