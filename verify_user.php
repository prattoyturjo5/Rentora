<?php
session_start();
require_once('DBconnect.php');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id > 0) {
    if ($action === 'approve') {
        $sql = "UPDATE users SET status = 'Verified' WHERE user_id = $user_id";
        mysqli_query($conn, $sql);
        header("Location: admin.php?msg=user_approved");
        exit();
    } elseif ($action === 'reject') {
        $sql = "UPDATE users SET status = 'Rejected' WHERE user_id = $user_id";
        mysqli_query($conn, $sql);
        header("Location: admin.php?msg=user_rejected");
        exit();
    }
}

header("Location: admin.php");
exit();
?>
