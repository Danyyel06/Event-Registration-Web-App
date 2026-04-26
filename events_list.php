<?php
require_once __DIR__ . '/config/db.php';

$events = $pdo->query('
    SELECT e.*,
           COUNT(r.id) AS reg_count
    FROM events e
    LEFT JOIN registrations r ON r.event_id = e.id
    WHERE e.date >= CURDATE()
    GROUP BY e.id
    ORDER BY e.date ASC
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events – Faculty</title>
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
        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 2rem;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .logo-mark {
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

        .logo-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.01em;
        }

        .header-cta {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            background: var(--black);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            transition: opacity 0.15s;
            letter-spacing: 0.01em;
        }

        .header-cta:hover { opacity: 0.8; }

        /* ── PAGE HERO ── */
        .hero {
            max-width: 1100px;
            margin: 0 auto;
            padding: 3.5rem 2rem 2rem;
        }

        .hero-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--ink-muted);
            margin-bottom: 0.75rem;
        }

        .hero h1 {
            font-size: clamp(2rem, 5vw, 3.25rem);
            font-weight: 800;
            color: var(--black);
            line-height: 1.08;
            letter-spacing: -0.03em;
            margin-bottom: 0.75rem;
        }

        .hero p {
            font-size: 1rem;
            color: var(--ink-soft);
            font-weight: 400;
        }

        /* ── CONTENT ── */
        .wrap {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 2rem 5rem;
        }

        .section-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.5rem 0 1rem;
            border-top: 1px solid var(--border);
            margin-bottom: 1.25rem;
        }

        .section-bar-label {
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--ink-muted);
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        /* ── GRID ── */
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1px;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            background: var(--border);
        }

        /* ── EVENT CARD ── */
        .card {
            background: var(--surface);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: background 0.1s;
            animation: fadeUp 0.4s ease both;
        }

        .card:hover { background: var(--hover); }

        <?php foreach (range(1, 20) as $i): ?>
        .card:nth-child(<?= $i ?>) { animation-delay: <?= ($i - 1) * 0.04 ?>s; }
        <?php endforeach; ?>

        .card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.5rem;
        }

        .card-date-block {
            flex-shrink: 0;
            text-align: center;
            width: 44px;
        }

        .card-date-day {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--black);
            line-height: 1;
            letter-spacing: -0.02em;
        }

        .card-date-month {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--ink-muted);
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .card-status {
            font-size: 0.68rem;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 999px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border: 1px solid;
        }

        .status-open   { color: #166534; background: #f0fdf4; border-color: #bbf7d0; }
        .status-soon   { color: #92400e; background: #fffbeb; border-color: #fde68a; }
        .status-full   { color: #991b1b; background: #fef2f2; border-color: #fecaca; }
        .status-today  { color: #1e40af; background: #eff6ff; border-color: #bfdbfe; }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--black);
            letter-spacing: -0.01em;
            line-height: 1.3;
        }

        .card-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .meta-line {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            color: var(--ink-soft);
        }

        .meta-line svg {
            width: 12px;
            height: 12px;
            flex-shrink: 0;
            opacity: 0.5;
        }

        .card-desc {
            font-size: 0.82rem;
            color: var(--ink-muted);
            line-height: 1.6;
        }

        /* Progress bar */
        .progress-wrap { display: flex; flex-direction: column; gap: 5px; }

        .progress-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.72rem;
            color: var(--ink-muted);
            font-weight: 500;
        }

        .bar-track {
            height: 3px;
            background: var(--border-light);
            border-radius: 999px;
            overflow: hidden;
        }

        .bar-fill {
            height: 100%;
            background: var(--black);
            border-radius: 999px;
        }

        .bar-fill.full { background: #ef4444; }
        .bar-fill.warn { background: #f59e0b; }

        .spots-note {
            font-size: 0.72rem;
            color: var(--ink-muted);
        }

        .spots-note.low  { color: #92400e; font-weight: 600; }
        .spots-note.full { color: #991b1b; font-weight: 600; }

        /* CTA Button */
        .reg-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 1rem;
            border-radius: 6px;
            font-size: 0.83rem;
            font-weight: 700;
            text-decoration: none;
            letter-spacing: 0.01em;
            transition: opacity 0.15s;
            font-family: var(--font);
        }

        .reg-btn.open {
            background: var(--black);
            color: white;
        }

        .reg-btn.open:hover { opacity: 0.8; }

        .reg-btn.disabled {
            background: var(--border-light);
            color: var(--ink-faint);
            cursor: not-allowed;
        }

        /* ── EMPTY ── */
        .empty {
            text-align: center;
            padding: 6rem 2rem;
            color: var(--ink-muted);
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--surface);
        }

        .empty-icon { font-size: 2rem; opacity: 0.35; margin-bottom: 1rem; display: block; }

        .empty h3 {
            font-size: 1rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.4rem;
        }

        .empty p { font-size: 0.85rem; }

        /* ── FOOTER ── */
        footer {
            border-top: 1px solid var(--border);
            text-align: center;
            padding: 1.5rem;
            font-size: 0.75rem;
            color: var(--ink-faint);
        }

        @media (max-width: 640px) {
            .grid { grid-template-columns: 1fr; }
            .hero { padding: 2rem 1.25rem 1.5rem; }
            .wrap { padding: 0 1.25rem 4rem; }
            .header-inner { padding: 0 1.25rem; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<header>
    <div class="header-inner">
        <a href="events_list.php" class="logo">
            <div class="logo-mark">🎓</div>
            <span class="logo-name">Faculty Events</span>
        </a>
        <a href="register.php" class="header-cta">Register for an event →</a>
    </div>
</header>

<div class="hero">
    <p class="hero-label">Student Portal</p>
    <h1>Upcoming Events</h1>
    <p>Browse and register for faculty events — no paper forms needed.</p>
</div>

<div class="wrap">
    <?php if (empty($events)): ?>
        <div class="empty">
            <span class="empty-icon">📭</span>
            <h3>No upcoming events</h3>
            <p>Check back soon — new events are added regularly.</p>
        </div>
    <?php else: ?>
        <div class="section-bar">
            <span class="section-bar-label"><?= count($events) ?> event<?= count($events) !== 1 ? 's' : '' ?> available</span>
        </div>
        <div class="grid">
            <?php foreach ($events as $event): ?>
                <?php
                    $today     = new DateTime('today');
                    $eventDate = new DateTime($event['date']);
                    $diff      = $today->diff($eventDate);
                    $days_left = (int)$diff->days;

                    if ($days_left === 0)      { $status_text = 'Today';          $status_class = 'status-today'; }
                    elseif ($days_left <= 7)   { $status_text = $days_left.'d left'; $status_class = 'status-soon'; }
                    else                       { $status_text = 'Upcoming';       $status_class = 'status-open'; }

                    $spots_left = $event['capacity'] - $event['reg_count'];
                    $is_full    = $spots_left <= 0;
                    $fill_pct   = min(100, round(($event['reg_count'] / max(1, $event['capacity'])) * 100));

                    if ($is_full)              { $bar_class = 'full'; $spots_class = 'full'; $spots_msg = 'Fully booked'; $status_text = 'Full'; $status_class = 'status-full'; }
                    elseif ($spots_left <= 5)  { $bar_class = 'warn'; $spots_class = 'low';  $spots_msg = "Only $spots_left spots left"; }
                    else                       { $bar_class = '';     $spots_class = '';     $spots_msg = "$spots_left spots remaining"; }
                ?>
                <div class="card">
                    <div class="card-top">
                        <div class="card-date-block">
                            <div class="card-date-day"><?= date('d', strtotime($event['date'])) ?></div>
                            <div class="card-date-month"><?= date('M', strtotime($event['date'])) ?></div>
                        </div>
                        <span class="card-status <?= $status_class ?>"><?= $status_text ?></span>
                    </div>

                    <div>
                        <div class="card-title"><?= htmlspecialchars($event['title']) ?></div>
                    </div>

                    <div class="card-meta">
                        <div class="meta-line">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            <?= htmlspecialchars($event['venue']) ?>
                        </div>
                        <div class="meta-line">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?= date('l, d F Y', strtotime($event['date'])) ?>
                        </div>
                        <div class="meta-line">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                            <?= $event['capacity'] ?> capacity
                        </div>
                    </div>

                    <?php if (!empty($event['description'])): ?>
                        <p class="card-desc"><?= htmlspecialchars(substr($event['description'], 0, 100)) ?><?= strlen($event['description']) > 100 ? '…' : '' ?></p>
                    <?php endif; ?>

                    <div class="progress-wrap">
                        <div class="progress-labels">
                            <span><?= $event['reg_count'] ?>/<?= $event['capacity'] ?> registered</span>
                            <span><?= $fill_pct ?>%</span>
                        </div>
                        <div class="bar-track">
                            <div class="bar-fill <?= $bar_class ?>" style="width:<?= $fill_pct ?>%"></div>
                        </div>
                        <span class="spots-note <?= $spots_class ?>"><?= $spots_msg ?></span>
                    </div>

                    <a href="register.php?event_id=<?= $event['id'] ?>" class="reg-btn <?= $is_full ? 'disabled' : 'open' ?>">
                        <?= $is_full ? 'Fully Booked' : 'Register →' ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer>&copy; <?= date('Y') ?> Faculty Events Portal &nbsp;·&nbsp; All rights reserved</footer>

</body>
</html>