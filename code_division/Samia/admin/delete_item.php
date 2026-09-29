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
        $checkRentals = $pdo->prepare("SELECT COUNT(*) AS total FROM rental_agreement WHERE equipment_id = :id");
        $checkRentals->execute(['id' => $item_id]);
        $rentalCount = (int)($checkRentals->fetch()['total'] ?? 0);

        $checkExchanges = $pdo->prepare("SELECT COUNT(*) AS total FROM exchange_agreement WHERE equipment_a_id = :id1 OR equipment_b_id = :id2");
        $checkExchanges->execute(['id1' => $item_id, 'id2' => $item_id]);
        $exchangeCount = (int)($checkExchanges->fetch()['total'] ?? 0);

        if ($rentalCount > 0 || $exchangeCount > 0) {
            $archiveStmt = $pdo->prepare("UPDATE equipment SET availability_status = 'Unavailable' WHERE equipment_id = :id");
            $archiveStmt->execute(['id' => $item_id]);
            header("Location: dashboard.php?msg=item_archived");
            exit();
        } else {
            $stmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = :id");
            $stmt->execute(['id' => $item_id]);
            header("Location: dashboard.php?msg=item_deleted");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: dashboard.php?msg=error");
        exit();
    }
}

header("Location: dashboard.php");
exit();
