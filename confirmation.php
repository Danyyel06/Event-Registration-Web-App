<?php
session_start();

if (!isset($_SESSION['reg_code'])) {
    header('Location: register.php');
    exit;
}

$reg_code     = $_SESSION['reg_code'];
$event_title  = $_SESSION['event_title'];
$student_name = $_SESSION['student_name'];

unset($_SESSION['reg_code'], $_SESSION['event_title'], $_SESSION['student_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmed</title>
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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            -webkit-font-smoothing: antialiased;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            animation: popIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.95) translateY(16px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Top section — success block */
        .card-top {
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border-light);
        }

        .check-ring {
            width: 52px;
            height: 52px;
            border: 1.5px solid var(--border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            background: var(--surface);
            animation: ringIn 0.5s ease 0.2s both;
        }

        @keyframes ringIn {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }

        .check-icon {
            font-size: 1.25rem;
        }

        .card-title {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--black);
            letter-spacing: -0.025em;
            margin-bottom: 0.35rem;
        }

        .card-sub {
            font-size: 0.83rem;
            color: var(--ink-muted);
            line-height: 1.5;
        }

        .card-sub strong {
            color: var(--ink);
            font-weight: 600;
        }

        /* Details rows */
        .details {
            border-bottom: 1px solid var(--border-light);
        }

        .detail-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1.75rem;
            border-bottom: 1px solid var(--border-light);
        }

        .detail-row:last-child { border-bottom: none; }

        .detail-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--ink-faint);
            text-transform: uppercase;
            letter-spacing: 0.07em;
        }

        .detail-value {
            font-size: 0.83rem;
            font-weight: 600;
            color: var(--ink);
            text-align: right;
            max-width: 60%;
        }

        .detail-value.confirmed {
            color: #166534;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Code section */
        .code-section {
            padding: 1.5rem 1.75rem;
            border-bottom: 1px solid var(--border-light);
            background: #fafafa;
        }

        .code-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--ink-faint);
            margin-bottom: 0.625rem;
        }

        .code-value {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--black);
            letter-spacing: 0.12em;
        }

        /* Notes */
        .notes {
            padding: 1.25rem 1.75rem;
            border-bottom: 1px solid var(--border-light);
        }

        .notes-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--ink-faint);
            margin-bottom: 0.75rem;
        }

        .notes ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .notes li {
            font-size: 0.8rem;
            color: var(--ink-soft);
            display: flex;
            gap: 8px;
            line-height: 1.5;
        }

        .notes li::before {
            content: '—';
            color: var(--ink-faint);
            flex-shrink: 0;
        }

        /* CTA */
        .card-cta {
            padding: 1.25rem 1.75rem;
        }

        .home-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.75rem;
            background: var(--black);
            color: white;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.83rem;
            font-weight: 700;
            transition: opacity 0.15s;
            letter-spacing: 0.01em;
        }

        .home-btn:hover { opacity: 0.8; }

        @media (max-width: 480px) {
            .card-top { padding: 2rem 1.25rem 1.5rem; }
            .detail-row { padding: 0.75rem 1.25rem; }
            .code-section { padding: 1.25rem; }
            .notes, .card-cta { padding: 1rem 1.25rem; }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="card-top">
        <div class="check-ring">
            <span class="check-icon">✓</span>
        </div>
        <h1 class="card-title">You're registered.</h1>
        <p class="card-sub">Welcome, <strong><?= htmlspecialchars($student_name) ?></strong> — your spot has been confirmed.</p>
    </div>

    <div class="details">
        <div class="detail-row">
            <span class="detail-label">Event</span>
            <span class="detail-value"><?= htmlspecialchars($event_title) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Name</span>
            <span class="detail-value"><?= htmlspecialchars($student_name) ?></span>
        </div>
        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value confirmed">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Confirmed
            </span>
        </div>
    </div>

    <div class="code-section">
        <div class="code-label">Registration Code</div>
        <div class="code-value"><?= htmlspecialchars($reg_code) ?></div>
    </div>

    <div class="notes">
        <div class="notes-label">Important</div>
        <ul>
            <li>Screenshot or write down your code — you'll need it at check-in.</li>
            <li>Present this code at the entrance for verification.</li>
            <li>Your code is unique to you. Do not share it.</li>
        </ul>
    </div>

    <div class="card-cta">
        <a href="events_list.php" class="home-btn">← Back to all events</a>
    </div>
</div>

</body>
</html>