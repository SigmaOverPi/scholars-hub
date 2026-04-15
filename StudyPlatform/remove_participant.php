<?php
declare(strict_types=1);
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/config/database.php";
requireLogin();

$sessionId = (int) ($_GET["session_id"] ?? 0);
$participantId = (int) ($_GET["participant_id"] ?? 0);
$userId = currentUserId();

if ($sessionId > 0 && $participantId > 0) {
    $ownerStmt = $pdo->prepare("SELECT creator_id FROM study_sessions WHERE id = :id");
    $ownerStmt->execute(["id" => $sessionId]);
    $session = $ownerStmt->fetch();

    if (!$session) {
        setFlash("error", "Session not found.");
    } elseif ((int) $session["creator_id"] !== $userId) {
        setFlash("error", "Only the session creator can remove participants.");
    } elseif ($participantId === $userId) {
        setFlash("error", "Use delete session logic to remove yourself as creator.");
    } else {
        $removeStmt = $pdo->prepare(
            "DELETE FROM session_participants WHERE session_id = :session_id AND user_id = :participant_id"
        );
        $removeStmt->execute(["session_id" => $sessionId, "participant_id" => $participantId]);
        setFlash("success", "Participant removed from session.");
    }
}

header("Location: session_details.php?id=" . $sessionId);
exit;
