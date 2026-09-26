<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/config/db.php');
require_once(__DIR__ . '/includes/auth_guard.php');

if (!is_member()) {
    header("Location: auth/login.php?msg=login_required");
    exit();
}

$lender_a_id = intval($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
$member_status = get_member_status($pdo, $lender_a_id);

if ($member_status !== 'Verified') {
    $err = ($member_status === 'Rejected') ? 'account_rejected' : 'account_pending';
    header("Location: item-details.php?id=" . intval($_POST['target_equipment_id'] ?? 0) . "&error=" . $err);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $equipment_b_id = intval($_POST['target_equipment_id'] ?? $_POST['item_id'] ?? 0);
    $equipment_a_id = intval($_POST['offered_equipment_id'] ?? 0);
    $lender_b_id = intval($_POST['lender_b_id'] ?? 0);

    if ($lender_b_id <= 0 && $equipment_b_id > 0) {
        $ownerStmt = $pdo->prepare("SELECT owner_id FROM equipment WHERE equipment_id = :id LIMIT 1");
        $ownerStmt->execute(['id' => $equipment_b_id]);
        $lender_b_id = intval($ownerStmt->fetchColumn());
    }

    if ($equipment_a_id <= 0 || $equipment_b_id <= 0) {
        header("Location: item-details.php?id=" . $equipment_b_id . "&error=missing_gear");
        exit();
    }

    if ($lender_a_id === $lender_b_id) {
        header("Location: item-details.php?id=" . $equipment_b_id . "&error=self_swap_forbidden");
        exit();
    }

    if ($equipment_a_id === $equipment_b_id) {
        header("Location: item-details.php?id=" . $equipment_b_id . "&error=same_item_swap");
        exit();
    }

    $swap_date = trim($_POST['swap_date'] ?? date('Y-m-d'));
    $swap_time = trim($_POST['swap_time'] ?? '10:00');
    $exchange_datetime = date('Y-m-d H:i:s', strtotime("$swap_date $swap_time"));

    try {
        $ins = $pdo->prepare("
            INSERT INTO exchange_agreement (lender_a_id, lender_b_id, equipment_a_id, equipment_b_id, exchange_date, status)
            VALUES (:lender_a_id, :lender_b_id, :equipment_a_id, :equipment_b_id, :exchange_date, 'Pending')
        ");
        $ins->execute([
            'lender_a_id'    => $lender_a_id,
            'lender_b_id'    => $lender_b_id,
            'equipment_a_id' => $equipment_a_id,
            'equipment_b_id' => $equipment_b_id,
            'exchange_date'  => $exchange_datetime
        ]);

        header("Location: user/exchanges.php?msg=proposal_sent");
        exit();
    } catch (PDOException $e) {
        header("Location: item-details.php?id=" . $equipment_b_id . "&error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
