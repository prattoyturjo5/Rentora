<?php
/**
 * Rentora Database Connection
 * Single reusable PDO MySQL connection.
 */

$host = 'localhost';
$dbname = 'rentora_db';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Attempt connecting to the specific database
    $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // If rentora_db does not exist yet, attempt to create it and reconnect
    if ($e->getCode() == 1049) {
        try {
            $rootDsn = "mysql:host=$host;charset=$charset";
            $tempPdo = new PDO($rootDsn, $username, $password, $options);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $tempPdo = null;

            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $innerException) {
            die("Database initialization error: " . htmlspecialchars($innerException->getMessage()));
        }
    } else {
        die("Database connection failed: " . htmlspecialchars($e->getMessage()));
    }
}

$conn = @mysqli_connect($host, $username, $password, $dbname);
if ($conn) {
    mysqli_set_charset($conn, $charset);
}
