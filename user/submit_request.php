<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

require_member('../auth/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $renter_id = (int)$_SESSION['user_id'];
    $start_date = trim($_POST['rental_start_date'] ?? '');
    $end_date = trim($_POST['rental_end_date'] ?? '');
    $pickup_spot = trim($_POST['pickup_spot'] ?? 'Central Library Front Gate');

    try {
        $stmt = $pdo->prepare("SELECT daily_rate, security_deposit FROM equipment WHERE equipment_id = :id OR id = :id2 LIMIT 1");
        $stmt->execute(['id' => $item_id, 'id2' => $item_id]);
        $item = $stmt->fetch();

        if ($item) {
            $start_ts = strtotime($start_date);
            $end_ts = strtotime($end_date);
            $diff_days = max(1, round(($end_ts - $start_ts) / 86400));

            $total_rent = $diff_days * $item['daily_rate'];
            $deposit = $item['security_deposit'];
            $handover_token = 'TRX-' . rand(1000, 9999);

            $ins = $pdo->prepare("
                INSERT INTO rental_agreement (equipment_id, renter_id, start_date, end_date, total_rent, deposit, handover_token, pickup_spot, status) 
                VALUES (:item_id, :renter_id, :start_date, :end_date, :total_rent, :deposit, :token, :pickup_spot, 'Pending')
            ");
            $ins->execute([
                'item_id'     => $item_id,
                'renter_id'   => $renter_id,
                'start_date'  => $start_date,
                'end_date'    => $end_date,
                'total_rent'  => $total_rent,
                'deposit'     => $deposit,
                'token'       => $handover_token,
                'pickup_spot' => $pickup_spot
            ]);

            header("Location: dashboard.php?msg=requested&token=" . urlencode($handover_token));
            exit();
        } else {
            die("Invalid equipment requested.");
        }
    } catch (PDOException $e) {
        die("Error processing rental request: " . htmlspecialchars($e->getMessage()));
    }
} else {
    header("Location: ../index.php");
    exit();
}
