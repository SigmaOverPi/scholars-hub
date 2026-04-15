<?php
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ashesi Scholars Hub</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="topbar">
        <div class="container nav">
            
            <a class="brand logo-container" href="index.php">
                <img src="assets/img/logo.png" alt="Ashesi Scholars Hub Logo" class="logo">
                <span>Ashesi Scholars Hub</span>
            </a>

            <nav>
                <?php if (isLoggedIn()): ?>
                    <a href="index.php">Browse Sessions</a>
                    <a href="create_session.php">Create Session</a>
                    <a href="my_sessions.php">My Sessions</a>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container">
        <?php if ($flash): ?>
            <div class="alert <?= h($flash["type"]) ?>"><?= h($flash["message"]) ?></div>
        <?php endif; ?>