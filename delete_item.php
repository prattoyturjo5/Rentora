<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php?msg=login_required");
    exit();
}

if (isset($_GET['id'])) {
    $item_id = (int)$_GET['id'];
    $user_id = (int)$_SESSION['user_id'];

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
        mysqli_query($conn, "DELETE FROM items WHERE item_id = $item_id");
        header("Location: admin.php?msg=item_deleted");
        exit();
    } else {
        mysqli_query($conn, "DELETE FROM items WHERE item_id = $item_id AND owner_id = $user_id");
        header("Location: owner-dashboard.php?msg=item_deleted");
        exit();
    }
}

header("Location: index.php");
exit();
?>
