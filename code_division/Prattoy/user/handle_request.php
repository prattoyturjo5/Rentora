<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (($_SESSION['role'] ?? '') !== 'member') {
    header("Location: ../auth/login.php");
    exit();
}
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');


$action = $_GET['action'] ?? '';
$request_id = (int)($_GET['id'] ?? 0);

if ($request_id > 0) {
    try {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE rental_agreement SET status = 'Approved' WHERE rental_id = :id OR id = :id2");
            $stmt->execute(['id' => $request_id, 'id2' => $request_id]);
            header("Location: rentals.php?msg=request_approved");
            exit();
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE rental_agreement SET status = 'Rejected' WHERE rental_id = :id OR id = :id2");
            $stmt->execute(['id' => $request_id, 'id2' => $request_id]);
            header("Location: rentals.php?msg=request_rejected");
            exit();
        } elseif ($action === 'return') {
            $stmt = $pdo->prepare("UPDATE rental_agreement SET status = 'Returned' WHERE rental_id = :id OR id = :id2");
            $stmt->execute(['id' => $request_id, 'id2' => $request_id]);

            // Release item back to available
            $findEq = $pdo->prepare("SELECT equipment_id FROM rental_agreement WHERE rental_id = :id OR id = :id2 LIMIT 1");
            $findEq->execute(['id' => $request_id, 'id2' => $request_id]);
            $eq_id = $findEq->fetchColumn();

            if ($eq_id) {
                $upEq = $pdo->prepare("UPDATE equipment SET is_available = 1 WHERE equipment_id = :id OR id = :id2");
                $upEq->execute(['id' => $eq_id, 'id2' => $eq_id]);
            }

            header("Location: rentals.php?msg=item_returned");
            exit();
        }
    } catch (PDOException $e) {
        die("Error handling request: " . htmlspecialchars($e->getMessage()));
    }
}

header("Location: rentals.php");
exit();
