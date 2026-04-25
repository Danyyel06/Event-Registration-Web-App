<?php
require_once __DIR__ . '/config/db.php';

$events = $pdo->query('
    SELECT e.id, e.title, e.date, e.venue, e.capacity,
    COUNT(r.id) AS reg_count
    FROM events e
    LEFT JOIN registrations r ON r.event_id = e.id
    WHERE e.date >= CURDATE()
    GROUP BY e.id
    ORDER BY e.date ASC
')->fetchAll();

$success = false;
$error = '';
$reg_code = '';
$event_title = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id     = (int)($_POST['event_id'] ?? 0);
    $student_name = trim(htmlspecialchars($_POST['student_name'] ?? ''));
    $matric_no    = trim(htmlspecialchars($_POST['matric_no'] ?? ''));
    $email        = trim($_POST['email'] ?? '');

    $errors = [];
    if ($event_id < 1)           $errors[] = 'Please select an event.';
    if (empty($student_name))    $errors[] = 'Your full name is required.';
    if (empty($matric_no))       $errors[] = 'Matric number is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!empty($errors)) {
        $error = implode(' ', $errors);
    } else {
        $stmt = $pdo->prepare('SELECT title, capacity FROM events WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $event_id]);
        $event = $stmt->fetch();

        if (!$event) {
            $error = 'Selected event not found.';
        } else {
            $event_title = $event['title'];

            $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE event_id = :event_id');
            $stmt->execute([':event_id' => $event_id]);
            $current_count = (int)$stmt->fetchColumn();

            if ($current_count >= $event['capacity']) {
                $error = 'Sorry, this event is now fully booked (' . $event['capacity'] . '/' . $event['capacity'] . ' seats taken).';
            } else {
                $stmt = $pdo->prepare('SELECT id FROM registrations WHERE event_id = :eid AND matric_no = :mat LIMIT 1');
                $stmt->execute([':eid' => $event_id, ':mat' => $matric_no]);
                
                if ($stmt->fetch()) {
                    $error = 'You have already registered for this event.';
                } else {
                    $reg_code = 'EVT-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

                    $stmt = $pdo->prepare('
                        INSERT INTO registrations (event_id, student_name, matric_no, email, reg_code)
                        VALUES (:event_id, :name, :matric, :email, :code)
                    ');
                    
                    $stmt->execute([
                        ':event_id' => $event_id,
                        ':name'     => $student_name,
                        ':matric'   => $matric_no,
                        ':email'    => $email,
                        ':code'     => $reg_code
                    ]);

                    $success = true;
                    session_start();
                    $_SESSION['reg_code'] = $reg_code;
                    $_SESSION['event_title'] = $event_title;
                    $_SESSION['student_name'] = $student_name;

                    header('Location: confirmation.php');
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register for an Event</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        header { background: #1B3A5C; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        header a { color: #93C5FD; text-decoration: none; margin-left: 1rem; }
        .wrap { max-width: 580px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; }
        h2 { color: #1B3A5C; margin-top: 0; }
        label { display: block; margin: .8rem 0 .3rem; font-weight: bold; font-size: .9rem; color: #374151; }
        input, select { width: 100%; padding: .6rem .8rem; border: 1px solid #D1D5DB; border-radius: 5px; font-size: 1rem; box-sizing: border-box; margin-bottom: .4rem; }
        .hint { font-size: .8rem; color: #6B7280; margin-top: -.2rem; margin-bottom: .6rem; }
        button { margin-top: 1rem; width: 100%; padding: .75rem; background: #2563EB; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; }
        .error { background: #FEE2E2; color: #991B1B; padding: .8rem 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .back { display: inline-block; margin-top: 1rem; color: #2563EB; text-decoration: none; }
    </style>
</head>
<body>
    <header>
        <strong>Faculty Event Registration</strong>
        <a href="events_list.php">View All Events</a>
    </header>
    <div class="wrap">
        <h2>Register for an Event</h2>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label>Select Event *</label>
            <select name="event_id" required>
                <option value="">-- Choose an event --</option>
                <?php foreach ($events as $ev): ?>
                    <?php 
                        $spots = $ev['capacity'] - $ev['reg_count'];
                        $full = $spots <= 0;
                    ?>
                    <option value="<?= $ev['id'] ?>" <?= ($full ? 'disabled' : '') ?> <?= (isset($_POST['event_id']) && $_POST['event_id'] == $ev['id'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($ev['title']) ?> 
                        (<?= date('d M Y', strtotime($ev['date'])) ?>) 
                        <?= $full ? '- FULL' : '- ' . $spots . ' spots left' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Full Name *</label>
            <input type="text" name="student_name" value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>" required>

            <label>Matric Number *</label>
            <input type="text" name="matric_no" value="<?= htmlspecialchars($_POST['matric_no'] ?? '') ?>" required>
            <p class="hint">e.g. 220591000</p>

            <label>Email Address *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <button type="submit">Register Now</button>
        </form>
        <a href="events_list.php" class="back">← Back to Events</a>
    </div>
</body>
</html>