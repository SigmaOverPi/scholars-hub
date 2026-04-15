<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

auth_start();

$pageTitle = 'Study Platform';
$flash = '';
if (isset($_GET['error']) && $_GET['error'] === 'year') {
    $flash = 'You can only open sessions for your own year group.';
}

$user = current_user();

if ($user !== null) {
    $pdo = db();
    $year = (int) $user['year_group'];
    $stmt = $pdo->prepare(
        'SELECT s.id, s.course, s.description, s.major, s.start_time, s.end_time, s.cancelled_at, s.location, s.capacity, s.year_group,
                u.full_name AS creator_name,
                (SELECT COUNT(*) FROM session_participants sp WHERE sp.session_id = s.id) AS participant_count
         FROM study_sessions s
         JOIN users u ON u.id = s.creator_id
         WHERE s.year_group = ? AND s.cancelled_at IS NULL
         ORDER BY s.start_time ASC'
    );
    $stmt->execute([$year]);
    $sessions = $stmt->fetchAll();
    $pageTitle = 'Dashboard · Year ' . $year;
}

require __DIR__ . '/includes/layout_header.php';
?>

<?php if ($flash !== ''): ?>
    <p class="msg msg-error"><?= h($flash) ?></p>
<?php endif; ?>

<?php if ($user === null): ?>
    <section class="hero">
        <h1>Find study sessions with your cohort</h1>
        <p class="lede">Register with your year group, then use your dashboard to browse sessions for your year only.</p>
        <p class="hero-actions">
            <a class="btn btn-primary" href="register.php">Register</a>
            <a class="btn" href="login.php">Log in</a>
        </p>
    </section>
<?php else: ?>
    <h1>Dashboard</h1>
    <p class="meta">Study sessions for year <strong><?= h((string) $user['year_group']) ?></strong> (your cohort). Listing is not filtered by major.</p>

    <?php if (count($sessions) === 0): ?>
        <p>No sessions yet. <a href="create_session.php">Create the first one</a>.</p>
    <?php else: ?>
        <ul class="session-list">
            <?php foreach ($sessions as $s): ?>
                <li class="session-card">
                    <div class="session-card-head">
                        <div class="session-card-titles">
                            <h3><?= h($s['course']) ?></h3>
                            <p class="badge"><?= h($s['major']) ?></p>
                        </div>
                        <?= session_status_tag_html($s) ?>
                    </div>
                    <p><?= nl2br(h($s['description'])) ?></p>
                    <p class="meta">
                        <?= h(format_session_time_range((string) $s['start_time'], (string) $s['end_time'])) ?> ·
                        <?= h($s['location']) ?> ·
                        <?= (int) $s['participant_count'] ?> / <?= (int) $s['capacity'] ?> joined
                    </p>
                    <p class="meta">Host: <?= h($s['creator_name']) ?></p>
                    <p><a href="session_details.php?id=<?= (int) $s['id'] ?>">Details</a></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
