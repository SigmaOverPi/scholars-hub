<?php

/*
 * Creates a new study session.
 * Checks all input fields and ensures they are appropriate e.g start time and end time.
 * Restricts session year group to the user's year group.
 * Adds the session to the db and automatically adds the creator as a participant.
 * Redirects to the dashboard.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/constants.php';
require_once __DIR__ . '/includes/helpers.php';

require_login();
$user = current_user();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course = trim($_POST['course'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $startTimeRaw = trim($_POST['start_time'] ?? '');
    $endTimeRaw = trim($_POST['end_time'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $capacity = (int) ($_POST['capacity'] ?? 0);
    $yearGroup = (int) ($_POST['year_group'] ?? 0);
    $major = trim($_POST['major'] ?? '');

    $startNorm = normalize_datetime_local_input($startTimeRaw);
    $endNorm = normalize_datetime_local_input($endTimeRaw);

    if ($course === '' || $description === '' || $startNorm === null || $endNorm === null || $location === '' || $capacity < 1) {
        $error = 'Please fill in all required fields with valid values.';
    } elseif (!year_group_is_valid($yearGroup)) {
        $error = 'Invalid year group.';
    } elseif ($yearGroup !== (int) $user['year_group']) {
        $error = 'You can only create sessions for your own year group.';
    } elseif (!major_is_valid($major)) {
        $error = 'Please select a valid major.';
    } else {
        $startAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $startNorm);
        $endAt = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $endNorm);
        if (!$startAt instanceof DateTimeImmutable || !$endAt instanceof DateTimeImmutable || $endAt <= $startAt) {
            $error = 'End time must be after start time.';
        } else {
            $pdo = db();
            $stmt = $pdo->prepare(
                'INSERT INTO study_sessions (creator_id, course, description, year_group, major, start_time, end_time, location, capacity)
                 VALUES (:creator_id, :course, :description, :year_group, :major, :start_time, :end_time, :location, :capacity)'
            );
            $joinStmt = $pdo->prepare(
                'INSERT INTO session_participants (session_id, user_id) VALUES (?, ?)'
            );
            $pdo->beginTransaction();
            $createdOk = false;
            try {
                $stmt->execute([
                    'creator_id' => $user['id'],
                    'course' => $course,
                    'description' => $description,
                    'year_group' => $yearGroup,
                    'major' => $major,
                    'start_time' => $startNorm,
                    'end_time' => $endNorm,
                    'location' => $location,
                    'capacity' => $capacity,
                ]);
                $sessionId = (int) $pdo->lastInsertId();
                if ($sessionId < 1) {
                    throw new RuntimeException('Session insert failed');
                }
                $joinStmt->execute([$sessionId, $user['id']]);
                $pdo->commit();
                $createdOk = true;
            } catch (Throwable) {
                $pdo->rollBack();
                $error = 'Could not create session. Please try again.';
            }
            if ($createdOk) {
                header('Location: index.php');
                exit;
            }
        }
    }
}

$pageTitle = 'Create session';
require __DIR__ . '/includes/layout_header.php';
?>

<h1>Create a study session</h1>
<?php if ($error !== ''): ?>
    <p class="msg msg-error"><?= h($error) ?></p>
<?php endif; ?>

<form method="post" class="form-card">
    <label for="course">Course (code and name)</label>
    <input id="course" name="course" type="text" maxlength="100" placeholder="e.g. CS 331 Computer Organization and Architecture" required value="<?= h($_POST['course'] ?? '') ?>">

    <label for="description">Description</label>
    <textarea id="description" name="description" rows="4" required><?= h($_POST['description'] ?? '') ?></textarea>

    <label for="year_group">Year group</label>
    <select id="year_group" name="year_group" required>
        <?php foreach (YEAR_GROUP_VALUES as $y): ?>
            <?php
            $mine = (int) $user['year_group'] === $y;
            ?>
            <option value="<?= $y ?>" <?= $mine ? 'selected' : '' ?> <?= $mine ? '' : 'disabled' ?>><?= $y ?><?= $mine ? ' (your year)' : '' ?></option>
        <?php endforeach; ?>
    </select>
    <p class="field-hint">Only your registered year group can be selected.</p>

    <label for="major">Major</label>
    <select id="major" name="major" required>
        <option value="" disabled <?= !isset($_POST['major']) ? 'selected' : '' ?>>Select major</option>
        <?php foreach (MAJOR_VALUES as $m): ?>
            <option value="<?= h($m) ?>" <?= (isset($_POST['major']) && $_POST['major'] === $m) ? 'selected' : '' ?>><?= h($m) ?></option>
        <?php endforeach; ?>
    </select>

    <label for="start_time">Start time</label>
    <input id="start_time" name="start_time" type="datetime-local" required value="<?= h($_POST['start_time'] ?? '') ?>">

    <label for="end_time">End time</label>
    <input id="end_time" name="end_time" type="datetime-local" required value="<?= h($_POST['end_time'] ?? '') ?>">
    <p class="field-hint">End must be after start.</p>

    <label for="location">Location</label>
    <input id="location" name="location" type="text" maxlength="255" required value="<?= h($_POST['location'] ?? '') ?>">

    <label for="capacity">Capacity</label>
    <input id="capacity" name="capacity" type="number" min="1" max="500" required value="<?= h($_POST['capacity'] ?? '10') ?>">

    <button type="submit">Create session</button>
</form>

<?php require __DIR__ . '/includes/layout_footer.php'; ?>
