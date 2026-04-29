<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = $pageTitle ?? 'Ashesi Scholars Hub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <a class="logo" href="index.php">Ashesi Scholars Hub</a>
    <nav>
        <?php if ($u = current_user()): ?>
            <span class="nav-meta"><?= h($u['full_name']) ?> · Year <?= (int) $u['year_group'] ?><?= ($u['major'] ?? '') !== '' ? ' · ' . h((string) $u['major']) : '' ?></span>
            <a href="index.php">🎓 Dashboard</a>
            <a href="create_session.php">📕 Create session</a>
            <a href="my_sessions.php">🧑‍🎓 My sessions</a>
            <a href="logout.php">🚪 Log out</a>
        <?php else: ?>
            <a href="login.php">Log in</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
