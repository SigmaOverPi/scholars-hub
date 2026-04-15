<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_login();
$user = current_user();

$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    header('Location: index.php');
    exit;
}

$pdo = db();
$stmt = $pdo->prepare(
    'SELECT s.id, s.creator_id, s.course, s.description, s.major, s.year_group, s.start_time, s.end_time, s.cancelled_at, s.location, s.capacity,
            u.full_name AS creator_name
     FROM study_sessions s
     JOIN users u ON u.id = s.creator_id
     WHERE s.id = ?'
);
$stmt->execute([$id]);
$session = $stmt->fetch();
if (!$session) {
    header('Location: index.php');
    exit;
}

if ((int) $session['year_group'] !== (int) $user['year_group']) {
    header('Location: index.php?error=year');
    exit;
}

$isCreator = (int) $session['creator_id'] === (int) $user['id'];
if (session_row_is_cancelled($session) && !$isCreator) {
    header('Location: index.php');
    exit;
}

$joined = false;
$st2 = $pdo->prepare('SELECT 1 FROM session_participants WHERE session_id = ? AND user_id = ?');
$st2->execute([$id, $user['id']]);
$joined = (bool) $st2->fetch();

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM session_participants WHERE session_id = ?');
$countStmt->execute([$id]);
$participantCount = (int) $countStmt->fetchColumn();
$full = $participantCount >= (int) $session['capacity'];

$availability = session_availability_state($session);
$canJoin = !$joined && !$full && $availability === 'active';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['join'])) {
    if ($availability !== 'active') {
        $message = 'This session is no longer open to join.';
    } elseif ($full) {
        $message = 'This session is full.';
    } elseif ($joined) {
        $message = 'You are already in this session.';
    } else {
        $ins = $pdo->prepare('INSERT IGNORE INTO session_participants (session_id, user_id) VALUES (?, ?)');
        $ins->execute([$id, $user['id']]);
        header('Location: session_details.php?id=' . $id);
        exit;
    }
}

$pageTitle = $session['course'] . ' · Session';
require __DIR__ . '/includes/layout_header.php';
?>

<h1><?= h($session['course']) ?> session</h1>
<div class="session-detail-head">
    <p class="badge"><?= h($session['major']) ?> · Year <?= h((string) $session['year_group']) ?></p>
    <?= session_status_tag_html($session) ?>
</div>
<p><?= nl2br(h($session['description'])) ?></p>
<p class="meta">
    <?= h(format_session_time_range((string) $session['start_time'], (string) $session['end_time'])) ?> ·
    <?= h($session['location']) ?>
</p>
<p class="meta">Host: <?= h($session['creator_name']) ?></p>
<p class="meta"><?= $participantCount ?> / <?= (int) $session['capacity'] ?> participants</p>

<?php if ($message !== ''): ?>
    <p class="msg msg-error"><?= h($message) ?></p>
<?php endif; ?>

<?php if ($availability === 'cancelled'): ?>
    <p class="msg msg-error">This session has been cancelled.</p>
<?php elseif ($joined): ?>
    <p class="msg msg-ok">You are signed up for this session.</p>
    <?php if ($availability === 'expired'): ?>
        <p class="msg msg-error">This session has ended.</p>
    <?php endif; ?>
<?php elseif ($availability === 'expired'): ?>
    <p class="msg msg-error">This session has ended.</p>
<?php elseif ($canJoin): ?>
    <form method="post">
        <input type="hidden" name="join" value="1">
        <button type="submit" class="btn btn-primary">Join session</button>
    </form>
<?php elseif ($full): ?>
    <p class="msg msg-error">This session is full.</p>
<?php endif; ?>

<p><a href="index.php">Back to dashboard</a></p>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
