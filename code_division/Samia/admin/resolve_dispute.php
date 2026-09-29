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
$agreement_id = (int)($_GET['id'] ?? 0);

if ($agreement_id > 0) {
    try {
        $status = ($action === 'refund_renter') ? 'Returned' : 'Active';
        $stmt = $pdo->prepare("UPDATE rental_agreement SET status = :status WHERE rental_id = :id OR id = :id2");
        $stmt->execute(['status' => $status, 'id' => $agreement_id, 'id2' => $agreement_id]);
        
        header("Location: dashboard.php?msg=agreement_updated");
        exit();
    } catch (PDOException $e) {
        header("Location: dashboard.php?msg=error");
        exit();
    }
}

header("Location: dashboard.php");
exit();
