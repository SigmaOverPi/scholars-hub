<?php
//db configuration (currently configured for xampp!!)
declare(strict_types=1);

$dbHost = "localhost";
$dbName = "study_platform";
$dbUser = "root";
$dbPass = "";

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    die("Database connection failed. Update config/database.php credentials.");
}
