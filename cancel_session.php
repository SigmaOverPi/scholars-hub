<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_login();
$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cancel'])) {
    header('Location: my_sessions.php');
    exit;
}

$id = (int) ($_POST['session_id'] ?? 0);
if ($id < 1) {
    header('Location: my_sessions.php');
    exit;
}

$pdo = db();
$stmt = $pdo->prepare(
    'SELECT id FROM study_sessions WHERE id = ? AND creator_id = ? AND year_group = ? AND cancelled_at IS NULL'
);
$stmt->execute([$id, $user['id'], (int) $user['year_group']]);
if (!$stmt->fetch()) {
    header('Location: my_sessions.php');
    exit;
}

$upd = $pdo->prepare(
    'UPDATE study_sessions SET cancelled_at = CURRENT_TIMESTAMP WHERE id = ? AND creator_id = ?'
);
$upd->execute([$id, $user['id']]);

header('Location: my_sessions.php');
exit;
