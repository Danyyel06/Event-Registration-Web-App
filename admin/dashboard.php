<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$total_events = $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$total_regs   = $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
$upcoming     = $pdo->query('SELECT COUNT(*) FROM events WHERE date >= CURDATE()')->fetchColumn();

$recent_events = $pdo->query('
    SELECT e.title, e.date, e.capacity, COUNT(r.id) AS reg_count
    FROM events e
    LEFT JOIN registrations r ON r.event_id = e.id
    WHERE e.date >= CURDATE()
    GROUP BY e.id
    ORDER BY e.date ASC
    LIMIT 5
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --black:        #0a0a0a;
            --ink:          #1a1a1a;
            --ink-soft:     #525252;
            --ink-muted:    #737373;
            --ink-faint:    #a3a3a3;
            --border:       #e5e5e5;
            --border-light: #f0f0f0;
            --bg:           #fafafa;
            --surface:      #ffffff;
            --hover:        #f5f5f5;
            --font:         'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--ink);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ── NAV ── */
        nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 2rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .brand-mark {
            width: 28px;
            height: 28px;
            background: var(--black);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--ink);
        }

        .brand-tag {
            font-size: 0.68rem;
            color: var(--ink-faint);
            font-weight: 500;
            margin-left: 2px;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.125rem;
        }

        .nav-links a {
            font-size: 0.8rem;
            color: var(--ink-muted);
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 500;
            transition: color 0.15s, background 0.15s;
        }

        .nav-links a:hover {
            color: var(--ink);
            background: var(--hover);
        }

        .nav-links a.active {
            color: var(--ink);
            font-weight: 600;
        }

        .nav-links a.logout {
            border: 1px solid var(--border);
            color: var(--ink);
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .nav-user {
            font-size: 0.75rem;
            color: var(--ink-faint);
            padding: 0 0.75rem;
            border-right: 1px solid var(--border);
            margin-right: 0.25rem;
        }

        /* ── LAYOUT ── */
        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2.5rem 2rem 5rem;
        }

        .page-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }

        .page-eyebrow {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--ink-faint);
            margin-bottom: 0.4rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--black);
            letter-spacing: -0.03em;
        }

        .header-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            background: var(--black);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: opacity 0.15s;
        }

        .header-action:hover { opacity: 0.8; }

        /* ── STATS ── */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            background: var(--border);
            margin-bottom: 2.5rem;
        }

        .stat {
            background: var(--surface);
            padding: 1.5rem 1.75rem;
            transition: background 0.15s;
            animation: fadeUp 0.35s ease both;
        }

        .stat:hover { background: var(--hover); }

        .stat:nth-child(1) { animation-delay: 0s; }
        .stat:nth-child(2) { animation-delay: 0.06s; }
        .stat:nth-child(3) { animation-delay: 0.12s; }

        .stat-label {
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--ink-faint);
            margin-bottom: 0.875rem;
        }

        .stat-num {
            font-size: 2.75rem;
            font-weight: 800;
            color: var(--black);
            letter-spacing: -0.04em;
            line-height: 1;
        }

        /* ── SECTION HEADING ── */
        .section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--ink-muted);
        }

        .section-link {
            font-size: 0.78rem;
            color: var(--ink-muted);
            text-decoration: none;
            font-weight: 500;
        }

        .section-link:hover { color: var(--ink); }

        /* ── ACTIONS ── */
        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1px;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            background: var(--border);
            margin-bottom: 2.5rem;
        }

        .action-card {
            background: var(--surface);
            padding: 1.25rem 1.5rem;
            text-decoration: none;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: background 0.1s;
        }

        .action-card:hover { background: var(--hover); }

        .action-icon {
            width: 36px;
            height: 36px;
            border: 1px solid var(--border);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
            background: var(--bg);
        }

        .action-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--black);
            margin-bottom: 2px;
        }

        .action-desc {
            font-size: 0.75rem;
            color: var(--ink-faint);
        }

        /* ── UPCOMING TABLE ── */
        .table-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            border-bottom: 1px solid var(--border-light);
            background: #fafafa;
        }

        th {
            padding: 0.75rem 1.25rem;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--ink-faint);
        }

        td {
            padding: 0.875rem 1.25rem;
            font-size: 0.83rem;
            color: var(--ink);
            border-bottom: 1px solid var(--border-light);
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--hover); }

        .td-title { font-weight: 600; color: var(--black); }
        .td-muted { color: var(--ink-muted); font-size: 0.78rem; }

        .progress-inline {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bar-sm {
            width: 60px;
            height: 3px;
            background: var(--border-light);
            border-radius: 999px;
            overflow: hidden;
        }

        .bar-sm-fill {
            height: 100%;
            background: var(--black);
            border-radius: 999px;
        }

        .bar-sm-fill.warn { background: #f59e0b; }
        .bar-sm-fill.full { background: #ef4444; }

        .empty-table {
            text-align: center;
            padding: 3rem;
            color: var(--ink-faint);
            font-size: 0.83rem;
        }

        @media (max-width: 768px) {
            .stats { grid-template-columns: 1fr; }
            .nav-user { display: none; }
            .wrap { padding: 1.75rem 1.25rem 4rem; }
            .nav-inner { padding: 0 1.25rem; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .wrap { animation: fadeUp 0.3s ease both; }
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
            <span class="nav-user">Hello, <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="events.php">Events</a>
            <a href="participants.php">Participants</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
</nav>

<div class="wrap">
    <div class="page-header">
        <div>
            <p class="page-eyebrow">Admin Panel</p>
            <h1 class="page-title">Dashboard</h1>
        </div>
        <a href="create_event.php" class="header-action">+ New event</a>
    </div>

    <!-- Stats -->
    <div class="stats">
        <div class="stat">
            <div class="stat-label">Total Events</div>
            <div class="stat-num"><?= $total_events ?></div>
        </div>
        <div class="stat">
            <div class="stat-label">Upcoming</div>
            <div class="stat-num"><?= $upcoming ?></div>
        </div>
        <div class="stat">
            <div class="stat-label">Registrations</div>
            <div class="stat-num"><?= $total_regs ?></div>
        </div>
    </div>

    <!-- Quick actions -->
    <div class="section-head">
        <span class="section-title">Quick Actions</span>
    </div>

    <div class="actions">
        <a href="create_event.php" class="action-card">
            <div class="action-icon">➕</div>
            <div>
                <div class="action-name">Create Event</div>
                <div class="action-desc">Add a new event</div>
            </div>
        </a>
        <a href="events.php" class="action-card">
            <div class="action-icon">📋</div>
            <div>
                <div class="action-name">Manage Events</div>
                <div class="action-desc">Edit or delete events</div>
            </div>
        </a>
        <a href="participants.php" class="action-card">
            <div class="action-icon">👥</div>
            <div>
                <div class="action-name">Participants</div>
                <div class="action-desc">View attendee lists</div>
            </div>
        </a>
    </div>

    <!-- Upcoming events table -->
    <div class="section-head">
        <span class="section-title">Upcoming Events</span>
        <a href="events.php" class="section-link">View all →</a>
    </div>

    <div class="table-card">
        <?php if (empty($recent_events)): ?>
            <div class="empty-table">No upcoming events yet. <a href="create_event.php" style="color:var(--ink);font-weight:600">Create one →</a></div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Registrations</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_events as $e): ?>
                    <?php
                        $pct  = min(100, round(($e['reg_count'] / max(1, $e['capacity'])) * 100));
                        $cls  = $pct >= 100 ? 'full' : ($pct >= 80 ? 'warn' : '');
                    ?>
                    <tr>
                        <td class="td-title"><?= htmlspecialchars($e['title']) ?></td>
                        <td class="td-muted"><?= date('d M Y', strtotime($e['date'])) ?></td>
                        <td>
                            <div class="progress-inline">
                                <div class="bar-sm">
                                    <div class="bar-sm-fill <?= $cls ?>" style="width:<?= $pct ?>%"></div>
                                </div>
                                <span class="td-muted"><?= $e['reg_count'] ?>/<?= $e['capacity'] ?></span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

</body>
</html>