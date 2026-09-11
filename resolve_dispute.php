<?php
session_start();
require_once('DBconnect.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$dispute_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($dispute_id > 0) {
    if ($action === 'refund_renter') {
        mysqli_query($conn, "UPDATE disputes SET status = 'Resolved' WHERE dispute_id = $dispute_id");
        $pay_sql = "UPDATE payments SET status = 'Refunded to Renter' WHERE request_id = (SELECT request_id FROM disputes WHERE dispute_id = $dispute_id)";
        mysqli_query($conn, $pay_sql);
        header("Location: admin.php?msg=dispute_refunded");
        exit();
    } elseif ($action === 'release_owner') {
        mysqli_query($conn, "UPDATE disputes SET status = 'Resolved' WHERE dispute_id = $dispute_id");
        $pay_sql = "UPDATE payments SET status = 'Released to Owner' WHERE request_id = (SELECT request_id FROM disputes WHERE dispute_id = $dispute_id)";
        mysqli_query($conn, $pay_sql);
        header("Location: admin.php?msg=dispute_released");
        exit();
    } elseif ($action === 'split_compromise') {
        mysqli_query($conn, "UPDATE disputes SET status = 'Resolved' WHERE dispute_id = $dispute_id");
        header("Location: admin.php?msg=dispute_split");
        exit();
    }
}

header("Location: admin.php");
exit();
?>
