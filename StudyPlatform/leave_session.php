<?php
declare(strict_types=1);
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/config/database.php";
requireLogin();

$sessionId = (int) ($_GET["id"] ?? 0);
$userId = currentUserId();

if ($sessionId > 0) {
    $creatorStmt = $pdo->prepare("SELECT creator_id FROM study_sessions WHERE id = :id");
    $creatorStmt->execute(["id" => $sessionId]);
    $session = $creatorStmt->fetch();

    if (!$session) {
        setFlash("error", "Session not found.");
    } elseif ((int) $session["creator_id"] === $userId) {
        setFlash("error", "As creator, you cannot leave your own session.");
    } else {
        $stmt = $pdo->prepare("DELETE FROM session_participants WHERE session_id = :session_id AND user_id = :user_id");
        $stmt->execute(["session_id" => $sessionId, "user_id" => $userId]);
        setFlash("success", "You left the session.");
    }
}

header("Location: index.php");
exit;
