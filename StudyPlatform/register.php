<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/constants.php';
require_once __DIR__ . '/includes/helpers.php';

auth_start();
if (current_user() !== null) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $yearGroup = (int) ($_POST['year_group'] ?? 0);
    $major = trim($_POST['major'] ?? '');

    if ($fullName === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!ashesi_registration_email_is_valid($email)) {
        $error = 'Use a valid Ashesi email address ending with @ashesi.edu.gh.';
    } elseif (!registration_password_is_valid($password)) {
        $error = 'Password must be at least 7 characters, include upper and lower case letters and a number, and have no spaces.';
    } elseif (!year_group_is_valid($yearGroup)) {
        $error = 'Please select a valid year group.';
    } elseif (!major_is_valid($major)) {
        $error = 'Please select a valid major.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'That email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare(
                'INSERT INTO users (full_name, email, password_hash, year_group, major) VALUES (?, ?, ?, ?, ?)'
            );
            $ins->execute([$fullName, $email, $hash, $yearGroup, $major]);
            $userId = (int) $pdo->lastInsertId();
            $_SESSION['user'] = [
                'id' => $userId,
                'full_name' => $fullName,
                'email' => $email,
                'year_group' => $yearGroup,
                'major' => $major,
            ];
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Register';
require __DIR__ . '/includes/layout_header.php';
?>
<h1>Create an account</h1>
<p class="lede">Your year group is fixed at registration and controls which study sessions you see. Major is stored for your profile only; session listings are by year group.</p>
<?php if ($error !== ''): ?>
    <p class="msg msg-error"><?= h($error) ?></p>
<?php endif; ?>
<form method="post" class="form-card">
    <label for="full_name">Full name</label>
    <input id="full_name" name="full_name" type="text" required maxlength="120" value="<?= h($_POST['full_name'] ?? '') ?>">

    <label for="email">Email</label>
    <input id="email" name="email" type="email" required maxlength="150" value="<?= h($_POST['email'] ?? '') ?>" title="Must be an @ashesi.edu.gh address" autocomplete="username">
    <p class="field-hint">Must end with <code>@ashesi.edu.gh</code> (case-insensitive).</p>

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required minlength="7" maxlength="200" autocomplete="new-password" title="At least 7 characters with upper, lower, number; no spaces" pattern="^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})\S$">
    <p class="field-hint">At least 7 characters, including an uppercase letter, a lowercase letter, and a digit. No spaces.</p>

    <label for="year_group">Year group</label>
    <select id="year_group" name="year_group" required>
        <option value="" disabled <?= !isset($_POST['year_group']) ? 'selected' : '' ?>>Select year</option>
        <?php foreach (YEAR_GROUP_VALUES as $y): ?>
            <option value="<?= $y ?>" <?= (isset($_POST['year_group']) && (int) $_POST['year_group'] === $y) ? 'selected' : '' ?>><?= $y ?></option>
        <?php endforeach; ?>
    </select>

    <label for="major">Major</label>
    <select id="major" name="major" required>
        <option value="" disabled <?= !isset($_POST['major']) ? 'selected' : '' ?>>Select major</option>
        <?php foreach (MAJOR_VALUES as $m): ?>
            <option value="<?= h($m) ?>" <?= (isset($_POST['major']) && $_POST['major'] === $m) ? 'selected' : '' ?>><?= h($m) ?></option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Register</button>
</form>
<p><a href="login.php">Already have an account? Log in</a></p>
<script>
(function () {
    var form = document.querySelector("form.form-card");
    if (!form) return;
    var emailEl = document.getElementById("email");
    var pwEl = document.getElementById("password");
    var ashesiSuffix = "@ashesi.edu.gh";
    var pwRe = /^((?=\S*?[A-Z])(?=\S*?[a-z])(?=\S*?[0-9]).{6,})\S$/;

    function ashesiEmailOk(email) {
        var t = email.trim();
        if (!t) return false;
        var i = t.lastIndexOf("@");
        if (i === -1) return false;
        return t.slice(i).toLowerCase() === ashesiSuffix;
    }

    form.addEventListener("submit", function (e) {
        var email = emailEl ? emailEl.value : "";
        var pw = pwEl ? pwEl.value : "";
        if (!ashesiEmailOk(email)) {
            e.preventDefault();
            alert("Please use an Ashesi email address ending with @ashesi.edu.gh.");
            if (emailEl) emailEl.focus();
            return;
        }
        if (!pwRe.test(pw)) {
            e.preventDefault();
            alert("Password must be at least 7 characters, include upper and lower case letters and a number, and have no spaces.");
            if (pwEl) pwEl.focus();
        }
    });
})();
</script>
<?php require __DIR__ . '/includes/layout_footer.php'; ?>
