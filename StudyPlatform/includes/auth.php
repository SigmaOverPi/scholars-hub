<?php

declare(strict_types=1);

require_once __DIR__ . '/db.php';

function auth_start(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * @return array{id:int,full_name:string,email:string,year_group:int,major:string}|null
 */
function current_user(): ?array
{
    auth_start();
    $u = $_SESSION['user'] ?? null;
    if (!is_array($u) || !isset($u['id'], $u['year_group'])) {
        return null;
    }
    if (!isset($u['major'])) {
        $u['major'] = '';
    }
    return $u;
}

function require_login(): void
{
    if (current_user() === null) {
        header('Location: login.php');
        exit;
    }
}
