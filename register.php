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
                $error = 'Sorry, this event is now fully booked.';
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for an Event – Faculty</title>
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
            --font:         'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--ink);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

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
        }

        .logo-name {
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--ink);
        }

        .back-link {
            font-size: 0.8rem;
            color: var(--ink-muted);
            text-decoration: none;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.15s;
        }

        .back-link:hover { color: var(--ink); }

        /* LAYOUT */
        .page {
            max-width: 560px;
            margin: 0 auto;
            padding: 3rem 2rem 5rem;
        }

        .page-eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--ink-muted);
            margin-bottom: 0.6rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--black);
            letter-spacing: -0.03em;
            line-height: 1.1;
            margin-bottom: 0.5rem;
        }

        .page-sub {
            color: var(--ink-soft);
            font-size: 0.875rem;
            margin-bottom: 2.5rem;
        }

        /* ERROR */
        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 0.9rem 1rem;
            margin-bottom: 1.5rem;
            color: #991b1b;
            font-size: 0.83rem;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        /* FORM */
        .form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
        }

        .form-section {
            padding: 1.75rem 1.75rem 0;
        }

        .form-section:last-of-type {
            padding-bottom: 1.75rem;
        }

        .section-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--ink-faint);
            padding: 0.9rem 1.75rem;
            border-top: 1px solid var(--border-light);
            border-bottom: 1px solid var(--border-light);
            background: #fafafa;
            margin: 0 -1.75rem 1.5rem;
        }

        .field {
            margin-bottom: 1.25rem;
        }

        .field:last-child { margin-bottom: 0; }

        .field label {
            display: block;
            font-size: 0.78rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.4rem;
        }

        .field-hint {
            font-size: 0.72rem;
            color: var(--ink-faint);
            margin-top: 0.3rem;
        }

        .field select,
        .field input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.875rem;
            font-family: var(--font);
            color: var(--ink);
            background: var(--surface);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            -webkit-appearance: none;
        }

        .field select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%23737373'%3E%3Cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
            padding-right: 2.25rem;
        }

        .field select:focus,
        .field input:focus {
            border-color: var(--black);
            box-shadow: 0 0 0 2px rgba(10,10,10,0.08);
        }

        .field input::placeholder { color: var(--ink-faint); }

        /* FOOTER */
        .form-footer {
            padding: 1.25rem 1.75rem;
            border-top: 1px solid var(--border-light);
            background: #fafafa;
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--black);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-family: var(--font);
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.15s;
            letter-spacing: 0.01em;
        }

        .submit-btn:hover { opacity: 0.85; }
        .submit-btn:active { opacity: 1; transform: scale(0.995); }

        @media (max-width: 540px) {
            .page { padding: 1.75rem 1.25rem 4rem; }
            .form-section { padding: 1.25rem 1.25rem 0; }
            .section-label { padding: 0.75rem 1.25rem; margin: 0 -1.25rem 1.25rem; }
            .form-footer { padding: 1rem 1.25rem; }
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .form-card { animation: fadeUp 0.3s ease both; }
    </style>
</head>
<body>

<header>
    <div class="header-inner">
        <a href="events_list.php" class="logo">
            <div class="logo-mark">🎓</div>
            <span class="logo-name">Faculty Events</span>
        </a>
        <a href="events_list.php" class="back-link">← Back to events</a>
    </div>
</header>

<div class="page">
    <p class="page-eyebrow">Student Portal</p>
    <h1 class="page-title">Register for<br>an Event</h1>
    <p class="page-sub">Fill in your details to secure your spot.</p>

    <?php if ($error): ?>
        <div class="error-box">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="post" action="">

            <div class="form-section">
                <div class="field">
                    <label for="event_id">Event *</label>
                    <select name="event_id" id="event_id" required>
                        <option value="">Select an event</option>
                        <?php foreach ($events as $ev): ?>
                            <?php $spots = $ev['capacity'] - $ev['reg_count']; $full = $spots <= 0; ?>
                            <option value="<?= $ev['id'] ?>"
                                <?= $full ? 'disabled' : '' ?>
                                <?= (isset($_POST['event_id']) && $_POST['event_id'] == $ev['id'] ? 'selected' : '') ?>>
                                <?= htmlspecialchars($ev['title']) ?> — <?= date('d M Y', strtotime($ev['date'])) ?>
                                <?= $full ? ' (Full)' : ' (' . $spots . ' left)' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="section-label" style="padding:0.9rem 1.75rem;border-top:1px solid #f0f0f0;font-size:0.68rem;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;color:#a3a3a3;background:#fafafa;">Your Details</div>

            <div class="form-section">
                <div class="field">
                    <label for="student_name">Full Name *</label>
                    <input type="text" id="student_name" name="student_name"
                           value="<?= htmlspecialchars($_POST['student_name'] ?? '') ?>"
                           placeholder="e.g. Amina Bello" required>
                </div>

                <div class="field">
                    <label for="matric_no">Matric Number *</label>
                    <input type="text" id="matric_no" name="matric_no"
                           value="<?= htmlspecialchars($_POST['matric_no'] ?? '') ?>"
                           placeholder="e.g. 220591000" required>
                    <p class="field-hint">Used to prevent duplicate registrations.</p>
                </div>

                <div class="field">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                           placeholder="you@university.edu.ng" required>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="submit-btn">Register Now →</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>