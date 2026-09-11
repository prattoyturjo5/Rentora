<?php
/**
 * Database Connection for Rentora
 * Standard procedural MySQLi connection pattern for university DBMS coursework
 */

$server = "localhost";
$username = "root";
$password = "";
$dbname = "rentora";

// Establish procedural mysqli connection
$conn = new mysqli($server, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Select the database
mysqli_select_db($conn, "rentora");

// Set character set to utf8mb4 for Bangladeshi currency symbol (৳) and text support
mysqli_set_charset($conn, "utf8mb4");
?>
