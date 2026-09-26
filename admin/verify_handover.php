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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['handover_token'])) {
    $token = strtoupper(trim($_POST['handover_token']));

    try {
        $checkStmt = $pdo->prepare("
            SELECT r.*, e.equipment_name AS title, CONCAT(m.first_name, ' ', m.last_name) AS renter_name 
            FROM rental_agreement r
            JOIN equipment e ON r.equipment_id = e.equipment_id 
            JOIN member m ON r.renter_id = m.member_id 
            WHERE UPPER(TRIM(r.handover_token)) = :token 
            AND r.status IN ('Pending', 'Active')
            LIMIT 1
        ");
        $checkStmt->execute(['token' => $token]);
        $rental = $checkStmt->fetch();

        if ($rental) {
            $req_id = $rental['rental_id'];
            $eq_id = $rental['equipment_id'];

            $up1 = $pdo->prepare("UPDATE rental_agreement SET status = 'Active' WHERE rental_id = :id");
            $up1->execute(['id' => $req_id]);

            $up2 = $pdo->prepare("UPDATE equipment SET availability_status = 'Rented' WHERE equipment_id = :id");
            $up2->execute(['id' => $eq_id]);

            header("Location: dashboard.php?msg=handover_success&renter=" . urlencode($rental['renter_name']));
            exit();
        } else {
            header("Location: dashboard.php?msg=invalid_token");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: dashboard.php?msg=error");
        exit();
    }
}

header("Location: dashboard.php");
exit();
