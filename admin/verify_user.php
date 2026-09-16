<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

require_admin('login.php');

$action = $_GET['action'] ?? '';
$user_id = (int)($_GET['id'] ?? 0);

if ($user_id > 0) {
    try {
        $status = ($action === 'approve') ? 'Verified' : 'Rejected';
        $stmt = $pdo->prepare("UPDATE member SET status = :status WHERE member_id = :id OR id = :id2");
        $stmt->execute(['status' => $status, 'id' => $user_id, 'id2' => $user_id]);
        
        header("Location: dashboard.php?msg=member_" . strtolower($status));
        exit();
    } catch (PDOException $e) {
        header("Location: dashboard.php?msg=error");
        exit();
    }
}

header("Location: dashboard.php");
exit();
