<?php

declare(strict_types=1);

(function (): void {
    $cfgPath = dirname(__DIR__) . '/config.php';
    $tz = 'Africa/Accra';
    if (is_file($cfgPath)) {
        $cfg = require $cfgPath;
        if (is_array($cfg) && !empty($cfg['timezone']) && is_string($cfg['timezone'])) {
            $tz = $cfg['timezone'];
        }
    }
    if (!in_array($tz, timezone_identifiers_list(), true)) {
        $tz = 'Africa/Accra';
    }
    date_default_timezone_set($tz);
})();

/** @return PDO */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $cfg = require dirname(__DIR__) . '/config.php';
    $db = $cfg['db'];
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $db['host'],
        $db['name'],
        $db['charset']
    );
    $pdo = new PDO($dsn, $db['user'], $db['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}
