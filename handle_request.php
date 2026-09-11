<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php?msg=login_required");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($request_id > 0) {
    if ($action === 'approve') {
        $sql = "UPDATE rental_requests SET status = 'Approved' WHERE request_id = $request_id";
        mysqli_query($conn, $sql);
        header("Location: owner-dashboard.php?msg=request_approved");
        exit();
    } elseif ($action === 'reject') {
        $sql = "UPDATE rental_requests SET status = 'Rejected' WHERE request_id = $request_id";
        mysqli_query($conn, $sql);
        header("Location: owner-dashboard.php?msg=request_rejected");
        exit();
    } elseif ($action === 'return') {
        // Mark rental as Returned
        $sql = "UPDATE rental_requests SET status = 'Returned' WHERE request_id = $request_id";
        mysqli_query($conn, $sql);

        // Make the item available again
        $item_sql = "UPDATE items SET is_available = 1 WHERE item_id = (SELECT item_id FROM rental_requests WHERE request_id = $request_id)";
        mysqli_query($conn, $item_sql);

        // Mark payment refunded for the deposit
        $pay_sql = "UPDATE payments SET status = 'Refunded to Renter' WHERE request_id = $request_id";
        mysqli_query($conn, $pay_sql);

        header("Location: owner-dashboard.php?msg=item_returned");
        exit();
    }
}

header("Location: owner-dashboard.php");
exit();
?>
