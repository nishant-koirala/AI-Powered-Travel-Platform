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

        // Re-initialize if: new file, tables missing, or packages table is empty
        // (covers a previous partial init that created tables but never inserted seed data)
        if (!$needsSetup) {
            $tableExists = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='packages'")->fetch();
            if (!$tableExists) {
                $needsSetup = true;
            } else {
                $count = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
                $needsSetup = ((int) $count === 0);
            }
        }

        if ($needsSetup && file_exists($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            // Split on ';' only outside BEGIN...END blocks so triggers stay intact.
            $stmts   = [];
            $buf     = '';
            $depth   = 0;
            foreach (preg_split('/\r?\n/', $sql) as $line) {
                $upper = strtoupper(trim($line));
                if ($upper === 'BEGIN') {
                    $depth++;
                } elseif ($upper === 'END' || $upper === 'END;') {
                    if ($depth > 0) $depth--;
                }
                $buf .= $line . "\n";
                if ($depth === 0 && substr(rtrim($line), -1) === ';') {
                    $stmt = trim(rtrim(rtrim($buf), ';'));
                    if ($stmt !== '' && strncmp(ltrim($stmt), '--', 2) !== 0) {
                        $stmts[] = $stmt;
                    }
                    $buf = '';
                }
            }
            if (trim($buf) !== '') {
                $stmts[] = trim($buf);
            }
            foreach ($stmts as $stmt) {
                try {
                    $pdo->exec($stmt);
                } catch (PDOException $stmtErr) {
                    error_log("[db_connect] SQLite schema stmt skipped: " . $stmtErr->getMessage());
                }
            }
        }

    } catch (PDOException $e2) {
        error_log("[db_connect] SQLite fallback also failed: " . $e2->getMessage());
        die("Could not connect to the database. Please try again later.");
    }
}
