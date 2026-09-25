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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $renter_id = (int)($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
    $start_date = trim($_POST['rental_start_date'] ?? date('Y-m-d'));
    $end_date = trim($_POST['rental_end_date'] ?? date('Y-m-d', strtotime('+1 day')));
    $pickup_spot = trim($_POST['pickup_spot'] ?? '');

    try {
        $stmt = $pdo->prepare("SELECT rental_rate, security_deposit, campus_spot FROM equipment WHERE equipment_id = :id LIMIT 1");
        $stmt->execute(['id' => $item_id]);
        $item = $stmt->fetch();

        if ($item) {
            $start_ts = strtotime($start_date);
            $end_ts = strtotime($end_date);
            $diff_days = max(1, round(($end_ts - $start_ts) / 86400));

            $total_cost = $diff_days * $item['rental_rate'];
            $deposit_amount = floatval($item['security_deposit'] ?? 0);
            if (empty($pickup_spot) && !empty($item['campus_spot'])) {
                $pickup_spot = $item['campus_spot'];
            }
            if (empty($pickup_spot)) {
                $pickup_spot = 'Hazari Lane';
            }
            $handover_token = 'TRX-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));

            $ins = $pdo->prepare("
                INSERT INTO rental_agreement (equipment_id, renter_id, start_date, expected_end_date, total_cost, deposit_amount, handover_token, pickup_spot, fine, status) 
                VALUES (:item_id, :renter_id, :start_date, :end_date, :total_cost, :deposit_amount, :handover_token, :pickup_spot, 0.00, 'Pending')
            ");
            $ins->execute([
                'item_id'        => $item_id,
                'renter_id'      => $renter_id,
                'start_date'     => $start_date,
                'end_date'       => $end_date,
                'total_cost'     => $total_cost,
                'deposit_amount' => $deposit_amount,
                'handover_token' => $handover_token,
                'pickup_spot'    => $pickup_spot
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
