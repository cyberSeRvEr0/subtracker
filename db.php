<?php
try {
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA journal_mode=WAL');
    $pdo->exec('PRAGMA synchronous=NORMAL');
    $pdo->exec('PRAGMA busy_timeout=500');
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}   