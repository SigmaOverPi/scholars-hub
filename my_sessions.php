<?php

/*
 * My Sessions Page
 * Displays sessions created by the user and sessions they have joined.
 * Prioritizes session display order.
 * Shows session status tags.
 * Allows session management actions:
 *   Cancel sessions (creator only, before expiry).
 *   Leave sessions (participants only, before expiry/cancellation).
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

require_login();
$user = current_user();
$yg = (int) $user['year_group'];

$pdo = db();
$stmt = $pdo->prepare(
    'SELECT s.id, s.course, s.description, s.major, s.start_time, s.end_time, s.cancelled_at, s.location, s.capacity,
            (SELECT COUNT(*) FROM session_participants sp WHERE sp.session_id = s.id) AS participant_count
     FROM study_sessions s
     WHERE s.creator_id = ? AND s.year_group = ?
     ORDER BY
    CASE
        WHEN NOW() BETWEEN s.start_time AND s.end_time THEN 0   -- ongoing
        WHEN s.start_time > NOW() THEN 1                        -- upcoming
        ELSE 2                                                  -- expired
    END,
    s.start_time ASC'
);
$stmt->execute([$user['id'], $yg]);
$created = $stmt->fetchAll();

$stmt2 = $pdo->prepare(
    'SELECT s.id, s.course, s.description, s.major, s.start_time, s.end_time, s.cancelled_at, s.location, s.capacity, u.full_name AS creator_name
     FROM study_sessions s
     JOIN session_participants sp ON sp.session_id = s.id AND sp.user_id = ?
     JOIN users u ON u.id = s.creator_id
     WHERE s.year_group = ? AND s.creator_id != ?
     ORDER BY
    CASE
        WHEN NOW() BETWEEN s.start_time AND s.end_time THEN 0   -- ongoing
        WHEN s.start_time > NOW() THEN 1                        -- upcoming
        ELSE 2                                                  -- expired
    END,
    s.start_time ASC'
);

$stmt2->execute([$user['id'], $yg, $user['id']]);
$joined = $stmt2->fetchAll();

$pageTitle = 'My sessions';
require __DIR__ . '/includes/layout_header.php';
?>

<h1>Sessions I created</h1>
<?php if (count($created) === 0): ?>
    <p>None yet.</p>
<?php else: ?>
    <ul class="session-list">
        <?php foreach ($created as $s): ?>
            <li class="session-card">
                <div class="session-card-head">
                    <div class="session-card-titles">
                        <h3><?= h($s['course']) ?></h3>
                        <p class="badge"><?= h($s['major']) ?></p>
                    </div>
                    <?= session_status_tag_html($s) ?>
                </div>
                <p><?= nl2br(h($s['description'])) ?></p>
                <p class="meta"><?= h(format_session_time_range((string) $s['start_time'], (string) $s['end_time'])) ?> · <?= h($s['location']) ?></p>
                <p class="meta"><?= (int) $s['participant_count'] ?> / <?= (int) $s['capacity'] ?> joined</p>
                <p><a href="session_details.php?id=<?= (int) $s['id'] ?>">Details</a></p>
                    <?php if (!session_row_is_cancelled($s) && session_availability_state($s) !== 'expired'): ?>                    <form method="post" action="cancel_session.php" class="session-cancel-wrap" onsubmit="return confirm('Cancel this session? This cannot be undone — it will be removed from the study directory for everyone and marked cancelled.');">
                        <input type="hidden" name="session_id" value="<?= (int) $s['id'] ?>">
                        <button type="submit" name="cancel" value="1" class="btn btn-danger">Cancel session</button>
                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<h2>Sessions I joined</h2>
<?php if (count($joined) === 0): ?>
    <p>None yet.</p>
<?php else: ?>
    <ul class="session-list">
        <?php foreach ($joined as $s): ?>
            <li class="session-card">
                <div class="session-card-head">
                    <div class="session-card-titles">
                        <h3><?= h($s['course']) ?></h3>
                        <p class="badge"><?= h($s['major']) ?></p>
                    </div>
                    <?= session_status_tag_html($s) ?>
                </div>
                <p><?= nl2br(h($s['description'])) ?></p>
                <p class="meta">Host: <?= h($s['creator_name']) ?></p>
                <p class="meta"><?= h(format_session_time_range((string) $s['start_time'], (string) $s['end_time'])) ?> · <?= h($s['location']) ?></p>
                <p><a href="session_details.php?id=<?= (int) $s['id'] ?>">Open</a></p>

                <?php if (
                    !session_row_is_cancelled($s) &&
                    session_availability_state($s) !== 'expired'
                ): ?>
                    <form method="post" action="leave_session.php"
                        onsubmit="return confirm('Are you sure you want to leave this session?');">

                        <input type="hidden" name="session_id" value="<?= (int) $s['id'] ?>">

                        <button type="submit" class="btn btn-secondary">
                            Leave session
                        </button>

                    </form>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
