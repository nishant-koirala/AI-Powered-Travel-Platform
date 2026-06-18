<?php

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "travel_website_db";

$dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("[db_connect] MySQL unavailable: " . $e->getMessage() . " — switching to SQLite fallback");

    $sqlitePath   = __DIR__ . '/travel_backup.sqlite';
    $schemaPath   = __DIR__ . '/sqlite_schema.sql';
    $needsSetup   = !file_exists($sqlitePath);

    try {
        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');

        // Re-initialize if file is new OR if a previous partial init left tables missing
        if (!$needsSetup) {
            $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='packages'")->fetch();
            $needsSetup = ($check === false);
        }

        if ($needsSetup && file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if ($stmt !== '') {
                    $pdo->exec($stmt);
                }
            }
        }

    } catch (PDOException $e2) {
        error_log("[db_connect] SQLite fallback also failed: " . $e2->getMessage());
        die("Could not connect to the database. Please try again later.");
    }
}
