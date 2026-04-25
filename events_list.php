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
    <title>Upcoming Events – Faculty</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #F1F5F9; margin: 0; }
        header { background: #1B3A5C; color: white; padding: 1.5rem 2rem; }
        header h1 { margin: 0; font-size: 1.5rem; }
        header p { margin: .3rem 0 0; color: #93C5FD; font-size: .9rem; }
        .wrap { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.2rem; }
        .card { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
        .card-top { background: #1B3A5C; padding: 1rem 1.2rem; }
        .card-top h3 { margin: 0; color: white; font-size: 1rem; }
        .card-top .venue { color: #93C5FD; font-size: .82rem; margin-top: .3rem; }
        .card-body { padding: 1rem 1.2rem; }
        .desc { font-size: .88rem; color: #6B7280; margin-bottom: .8rem; line-height: 1.5; }
        .meta { display: flex; justify-content: space-between; font-size: .85rem; color: #374151; margin-bottom: .8rem; }
        .countdown { display: inline-block; padding: .3rem .9rem; border-radius: 20px; font-weight: bold; font-size: .82rem; margin-bottom: .8rem; }
        .countdown.today { background: #FEF3C7; color: #92400E; }
        .countdown.soon  { background: #FEE2E2; color: #991B1B; }
        .countdown.weeks { background: #DBEAFE; color: #1E3A8A; }
        .countdown.far   { background: #DCFCE7; color: #166534; }
        .capacity-bar { background: #E5E7EB; border-radius: 999px; height: 6px; margin: .5rem 0 .3rem; overflow: hidden; }
        .capacity-fill { height: 100%; border-radius: 999px; background: #2563EB; }
        .capacity-fill.full { background: #DC2626; }
        .capacity-text { font-size: .78rem; color: #6B7280; margin-bottom: .8rem; }
        a.reg-btn { display: block; text-align: center; padding: .65rem; background: #2563EB; color: white; border-radius: 6px; text-decoration: none; font-size: .92rem; font-weight: bold; }
        a.reg-btn:hover { background: #1D4ED8; }
        a.reg-btn.disabled { background: #9CA3AF; pointer-events: none; }
        .no-events { text-align: center; padding: 3rem; color: #6B7280; }
    </style>
</head>
<body>
    <header>
        <h1>Faculty Events & Seminars</h1>
        <p>Register online – no paper forms required</p>
    </header>
    <div class="wrap">
        <?php if (empty($events)): ?>
            <div class="no-events"><p>No upcoming events at this time. Check back soon.</p></div>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($events as $event): ?>
                    <?php 
                        $today = new DateTime('today');
                        $eventDate = new DateTime($event['date']);
                        $diff = $today->diff($eventDate);
                        $days_left = (int)$diff->days;

                        if ($days_left === 0) {
                            $countdown_text = 'Today!';
                            $countdown_class = 'today';
                        } elseif ($days_left <= 7) {
                            $countdown_text = $days_left . ' day' . ($days_left === 1 ? '' : 's') . ' left';
                            $countdown_class = 'soon';
                        } elseif ($days_left <= 30) {
                            $countdown_text = $days_left . ' days left';
                            $countdown_class = 'weeks';
                        } else {
                            $countdown_text = $days_left . ' days away';
                            $countdown_class = 'far';
                        }

                        $spots_left = $event['capacity'] - $event['reg_count'];
                        $is_full = $spots_left <= 0;
                        $fill_pct = min(100, round(($event['reg_count'] / max(1, $event['capacity'])) * 100));
                    ?>
                    <div class="card">
                        <div class="card-top">
                            <h3><?= htmlspecialchars($event['title']) ?></h3>
                            <div class="venue">📍 <?= htmlspecialchars($event['venue']) ?></div>
                        </div>
                        <div class="card-body">
                            <span class="countdown <?= $countdown_class ?>">⏰ <?= $countdown_text ?></span>
                            <div class="meta">
                                <span>📅 <?= date('d M Y', strtotime($event['date'])) ?></span>
                                <span>🎓 <?= $event['capacity'] ?> seats</span>
                            </div>
                            <?php if (!empty($event['description'])): ?>
                                <p class="desc">
                                    <?= htmlspecialchars(substr($event['description'], 0, 120)) ?>
                                    <?= strlen($event['description']) > 120 ? '...' : '' ?>
                                </p>
                            <?php endif; ?>
                            <div class="capacity-bar">
                                <div class="capacity-fill <?= $is_full ? 'full' : '' ?>" style="width: <?= $fill_pct ?>%"></div>
                            </div>
                            <p class="capacity-text">
                                <?= $event['reg_count'] ?> / <?= $event['capacity'] ?> registered
                                <?= $is_full ? '– Fully Booked' : '(' . $spots_left . ' spots left)' ?>
                            </p>
                            <a href="register.php?event_id=<?= $event['id'] ?>" class="reg-btn <?= $is_full ? 'disabled' : '' ?>">
                                <?= $is_full ? 'Fully Booked' : 'Register Now →' ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>