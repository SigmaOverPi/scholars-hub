<?php

/*
 * Log Out.
 * Starts session handling.
 * Removes session variables.
 * Deletes the session cookie if it exists.
 * Destroys the session.
 * and then redirects the user back to the homepage.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';

auth_start();
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
header('Location: index.php');
exit;
