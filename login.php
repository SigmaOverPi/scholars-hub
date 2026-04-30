<?php
/*
 * Log in.
 * Check if user is already logged in and redirects if they are.
 * Validates credentials.
 * Compares them against the data in the database.
 * Creates session upon successful login.
 * Redirects the user to the dashboard.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

auth_start();
if (current_user() !== null) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($email === '' || $password === '') {
        $error = 'Please enter email and password.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, password_hash, year_group, major FROM users WHERE email = ?'
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($password, $row['password_hash'])) {
            $error = 'Invalid email or password.';
        } else {
            $_SESSION['user'] = [
                'id' => (int) $row['id'],
                'full_name' => $row['full_name'],
                'email' => $row['email'],
                'year_group' => (int) $row['year_group'],
                'major' => (string) ($row['major'] ?? ''),
            ];
            header('Location: index.php');
            exit;
        }
    }
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/layout_header.php';
?>
<h1 style="text-align: center;">Log in</h1>
<?php if ($error !== ''): ?>
    <p class="msg msg-error"><?= h($error) ?></p>
<?php endif; ?>
<form method="post" class="form-card">
    <label for="email">Email</label>
    <input id="email" name="email" type="email" required autocomplete="username" value="<?= h($_POST['email'] ?? '') ?>">

    <label for="password">Password</label>
    <input id="password" name="password" type="password" required autocomplete="current-password">

    <button type="submit">Log in</button>
</form>
<p style="text-align: center;"><a href="register.php">Need an account? Register</a></p>
<?php require __DIR__ . '/includes/layout_footer.php'; ?>
