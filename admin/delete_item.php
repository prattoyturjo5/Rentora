<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit();
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

$item_id = (int)($_GET['id'] ?? 0);

if ($item_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = :id");
        $stmt->execute(['id' => $item_id]);
        header("Location: dashboard.php?msg=item_deleted");
        exit();
    } catch (PDOException $e) {
        header("Location: dashboard.php?msg=error");
        exit();
    }
}

header("Location: dashboard.php");
exit();
