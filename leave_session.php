<?php
/*
 * Handles leaving study sessions.
 * Ensures request is POST so someone cant open this directly in a browser.
 * Validates the session ID.
 * Prevents users from leaving invalid sessions.
 * Prevents creators from leaving their own sessions.
 * Removes the user from session_participants.
 * Redirects to My Sessions page.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_login();

$user = current_user();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_sessions.php');
    exit;
}

$sessionId = (int) ($_POST['session_id'] ?? 0);

if ($sessionId < 1) {
    header('Location: my_sessions.php');
    exit;
}

$pdo = db();

//prevent leaving your own session
$stmt = $pdo->prepare('SELECT creator_id FROM study_sessions WHERE id = ?');
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session || (int)$session['creator_id'] === (int)$user['id']) {
    header('Location: my_sessions.php');
    exit;
}

//remove participant
$stmt = $pdo->prepare(
    'DELETE FROM session_participants WHERE session_id = ? AND user_id = ?'
);
$stmt->execute([$sessionId, $user['id']]);

header('Location: my_sessions.php');
exit;