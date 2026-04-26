<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute([':id' => $id]);
    header('Location: events.php?deleted=1');
    exit;
}

$events = $pdo->query('
    SELECT e.*, COUNT(r.id) AS reg_count
    FROM events e
    LEFT JOIN registrations r ON r.event_id = e.id
    GROUP BY e.id
    ORDER BY e.date ASC
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events – Admin</title>
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

        body { font-family: var(--font); background: var(--bg); color: var(--ink); min-height: 100vh; -webkit-font-smoothing: antialiased; }

        nav { background: var(--surface); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 100; }
        .nav-inner { max-width: 1100px; margin: 0 auto; padding: 0 2rem; height: 56px; display: flex; align-items: center; justify-content: space-between; }
        .nav-brand { display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .brand-mark { width: 28px; height: 28px; background: var(--black); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .brand-name { font-size: .875rem; font-weight: 700; color: var(--ink); }
        .brand-tag  { font-size: .68rem; color: var(--ink-faint); margin-left: 2px; }
        .nav-links { display: flex; align-items: center; gap: .125rem; }
        .nav-links a { font-size: .8rem; color: var(--ink-muted); text-decoration: none; padding: 6px 10px; border-radius: 6px; font-weight: 500; transition: color .15s, background .15s; }
        .nav-links a:hover { color: var(--ink); background: var(--hover); }
        .nav-links a.active { color: var(--ink); font-weight: 600; }
        .nav-links a.logout { border: 1px solid var(--border); color: var(--ink); font-weight: 600; margin-left: .5rem; }

        .wrap { max-width: 1100px; margin: 0 auto; padding: 2.5rem 2rem 5rem; }

        .page-header { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
        .page-eyebrow { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--ink-faint); margin-bottom: .4rem; }
        .page-title { font-size: 1.75rem; font-weight: 800; color: var(--black); letter-spacing: -.03em; }

        .create-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: var(--black); color: white; border-radius: 6px; text-decoration: none; font-size: .8rem; font-weight: 600; transition: opacity .15s; }
        .create-btn:hover { opacity: .8; }

        /* Toast */
        .toast { display: flex; align-items: center; gap: 8px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px; padding: .75rem 1rem; margin-bottom: 1.5rem; font-size: .83rem; color: #166534; font-weight: 500; }

        /* TABLE */
        .table-card { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        thead tr { border-bottom: 1px solid var(--border-light); background: #fafafa; }
        th { padding: .75rem 1.25rem; text-align: left; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--ink-faint); }
        td { padding: .875rem 1.25rem; font-size: .83rem; color: var(--ink); border-bottom: 1px solid var(--border-light); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--hover); }

        .td-title { font-weight: 600; color: var(--black); font-size: .875rem; }
        .td-muted  { color: var(--ink-muted); font-size: .78rem; }

        /* Badge */
        .badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 999px; font-size: .68rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; border: 1px solid; }
        .badge-open { color: #166534; background: #f0fdf4; border-color: #bbf7d0; }
        .badge-warn { color: #92400e; background: #fffbeb; border-color: #fde68a; }
        .badge-full { color: #991b1b; background: #fef2f2; border-color: #fecaca; }

        /* Progress */
        .prog-wrap { display: flex; align-items: center; gap: 8px; }
        .bar-sm { width: 56px; height: 3px; background: var(--border-light); border-radius: 999px; overflow: hidden; }
        .bar-sm-fill { height: 100%; background: var(--black); border-radius: 999px; }
        .bar-sm-fill.warn { background: #f59e0b; }
        .bar-sm-fill.full { background: #ef4444; }

        /* Actions */
        .row-actions { display: flex; align-items: center; gap: .375rem; }
        .btn-sm { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 5px; font-size: .75rem; font-weight: 600; text-decoration: none; transition: background .1s; cursor: pointer; border: 1px solid; font-family: var(--font); }
        .btn-view   { background: var(--surface); color: var(--ink-soft); border-color: var(--border); }
        .btn-edit   { background: var(--surface); color: var(--ink-soft); border-color: var(--border); }
        .btn-delete { background: var(--surface); color: #991b1b;       border-color: #fecaca; }
        .btn-view:hover   { background: var(--hover); color: var(--ink); }
        .btn-edit:hover   { background: var(--hover); color: var(--ink); }
        .btn-delete:hover { background: #fef2f2; }

        .empty-state { text-align: center; padding: 4rem 2rem; color: var(--ink-faint); font-size: .83rem; }
        .empty-state a { color: var(--ink); font-weight: 600; }

        @media (max-width: 640px) { .wrap { padding: 1.75rem 1.25rem 4rem; } .nav-inner { padding: 0 1.25rem; } }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .wrap { animation: fadeUp .3s ease both; }
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
            <a href="events.php" class="active">Events</a>
            <a href="participants.php">Participants</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
</nav>

<div class="wrap">
    <div class="page-header">
        <div>
            <p class="page-eyebrow">Admin Panel</p>
            <h1 class="page-title">All Events</h1>
        </div>
        <a href="create_event.php" class="create-btn">+ Create event</a>
    </div>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="toast">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Event deleted successfully.
        </div>
    <?php endif; ?>

    <div class="table-card">
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Registrations</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr><td colspan="6">
                            <div class="empty-state">
                                No events yet. <a href="create_event.php">Create your first event →</a>
                            </div>
                        </td></tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <?php
                                $spots_left = $event['capacity'] - $event['reg_count'];
                                $is_full    = $spots_left <= 0;
                                $fill_pct   = min(100, round(($event['reg_count'] / max(1, $event['capacity'])) * 100));
                                $bar_cls    = $is_full ? 'full' : ($spots_left <= 5 ? 'warn' : '');
                                if ($is_full)            { $badge = 'badge-full'; $badge_txt = 'Full'; }
                                elseif ($spots_left <= 5){ $badge = 'badge-warn'; $badge_txt = $spots_left.' left'; }
                                else                     { $badge = 'badge-open'; $badge_txt = 'Open'; }
                            ?>
                            <tr>
                                <td class="td-title"><?= htmlspecialchars($event['title']) ?></td>
                                <td class="td-muted"><?= date('d M Y', strtotime($event['date'])) ?></td>
                                <td class="td-muted"><?= htmlspecialchars($event['venue']) ?></td>
                                <td>
                                    <div class="prog-wrap">
                                        <div class="bar-sm"><div class="bar-sm-fill <?= $bar_cls ?>" style="width:<?= $fill_pct ?>%"></div></div>
                                        <span class="td-muted"><?= $event['reg_count'] ?>/<?= $event['capacity'] ?></span>
                                    </div>
                                </td>
                                <td><span class="badge <?= $badge ?>"><?= $badge_txt ?></span></td>
                                <td>
                                    <div class="row-actions">
                                        <a href="participants.php?event_id=<?= $event['id'] ?>" class="btn-sm btn-view">Attendees</a>
                                        <a href="edit_event.php?id=<?= $event['id'] ?>"         class="btn-sm btn-edit">Edit</a>
                                        <a href="events.php?delete=<?= $event['id'] ?>"         class="btn-sm btn-delete"
                                           onclick="return confirm('Delete this event and all registrations?')">Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>