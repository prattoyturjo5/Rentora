<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

require_admin('login.php');

$item_id = (int)($_GET['id'] ?? 0);

if ($item_id > 0) {
    try {
        $stmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = :id OR id = :id2");
        $stmt->execute(['id' => $item_id, 'id2' => $item_id]);
        header("Location: dashboard.php?msg=item_deleted");
        exit();
    } catch (PDOException $e) {
        header("Location: dashboard.php?msg=error");
        exit();
    }
}

header("Location: dashboard.php");
exit();
