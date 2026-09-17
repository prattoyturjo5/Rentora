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


$action = $_GET['action'] ?? '';
$user_id = (int)($_GET['id'] ?? 0);

if ($user_id > 0) {
    try {
        $status = ($action === 'approve' || $action === 'verify' || $action === 'Verified') ? 'Verified' : 'Rejected';
        $stmt = $pdo->prepare("UPDATE member SET status = :status WHERE member_id = :id");
        $stmt->execute(['status' => $status, 'id' => $user_id]);
        
        header("Location: dashboard.php?msg=member_" . strtolower($status));
        exit();
    } catch (PDOException $e) {
        header("Location: dashboard.php?msg=error");
        exit();
    }
}

header("Location: dashboard.php");
exit();
