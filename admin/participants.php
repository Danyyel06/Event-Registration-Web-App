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
        $stmt = $pdo->prepare('
            SELECT student_name, matric_no, email, reg_code, registered_at
            FROM registrations
            WHERE event_id = :event_id
            ORDER BY registered_at ASC
        ');
        $stmt->execute([':event_id' => $selected_event_id]);
        $students = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Participant List – Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        nav { background: #1B3A5C; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #93C5FD; text-decoration: none; margin-left: 1.5rem; }
        .wrap { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        h1 { color: #1B3A5C; }
        select { padding: .5rem .8rem; border-radius: 5px; border: 1px solid #D1D5DB; font-size: 1rem; }
        .info { background: #DBEAFE; border-left: 4px solid #2563EB; padding: 1rem 1.2rem; border-radius: 0 6px 6px 0; margin: 1.2rem 0; }
        .info h3 { margin: 0 0 .4rem; color: #1E3A8A; }
        .info p { margin: .2rem 0; font-size: .9rem; color: #1D4ED8; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th { background: #1B3A5C; color: white; padding: .8rem 1rem; text-align: left; font-size: .9rem; }
        td { padding: .75rem 1rem; border-bottom: 1px solid #E5E7EB; font-size: .88rem; }
        tr:last-child td { border-bottom: none; }
        .code { font-family: monospace; background: #EDE9FE; color: #5B21B6; padding: .2rem .5rem; border-radius: 4px; font-size: .85rem; }
        .print-btn { padding: .5rem 1.2rem; background: #166534; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: .9rem; }

        @media print {
            nav, select, form, .print-btn { display: none; }
            body { background: white; }
            .wrap { max-width: 100%; margin: 0; padding: 0; }
        }
    </style>
</head>
<body>
    <nav>
        <strong>Faculty Events – Admin</strong>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="events.php">Events</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    <div class="wrap">
        <h1>Participant List</h1>

        <form method="get" action="">
            <label><strong>Select Event:</strong></label>
            <select name="event_id" onchange="this.form.submit()">
                <option value="">-- Choose an event --</option>
                <?php foreach ($all_events as $ev): ?>
                    <option value="<?= $ev['id'] ?>" <?= ($selected_event_id == $ev['id'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($ev['title']) ?> (<?= date('d M Y', strtotime($ev['date'])) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if ($event): ?>
            <div class="info">
                <h3><?= htmlspecialchars($event['title']) ?></h3>
                <p>
                    Date: <?= date('l, d F Y', strtotime($event['date'])) ?> &nbsp;|&nbsp;
                    Venue: <?= htmlspecialchars($event['venue']) ?> &nbsp;|&nbsp;
                    Registered: <strong><?= count($students) ?></strong> / <?= $event['capacity'] ?> capacity
                </p>
            </div>

            <?php if (empty($students)): ?>
                <p style="color:#6B7280;">No students have registered for this event yet.</p>
            <?php else: ?>
                <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
                <table style="margin-top:1rem;">
                    <tr>
                        <th>#</th>
                        <th>Full Name</th>
                        <th>Matric No.</th>
                        <th>Email</th>
                        <th>Reg. Code</th>
                        <th>Registered At</th>
                    </tr>
                    <?php foreach ($students as $i => $s): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($s['student_name']) ?></td>
                        <td><?= htmlspecialchars($s['matric_no']) ?></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><span class="code"><?= htmlspecialchars($s['reg_code']) ?></span></td>
                        <td><?= date('d M Y, g:ia', strtotime($s['registered_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>