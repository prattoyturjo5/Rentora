<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php?msg=login_required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_title'])) {
    $owner_id = (int)$_SESSION['user_id'];
    $title = mysqli_real_escape_string($conn, trim($_POST['item_title']));
    $category_id = (int)$_POST['category_id'];
    $condition = mysqli_real_escape_string($conn, trim($_POST['item_condition']));
    $pickup_spot = mysqli_real_escape_string($conn, trim($_POST['pickup_spot']));
    $daily_rate = floatval($_POST['daily_rate']);
    $security_deposit = floatval($_POST['security_deposit']);
    $description = mysqli_real_escape_string($conn, trim($_POST['item_description']));
    $image_url = !empty($_POST['image_url']) ? mysqli_real_escape_string($conn, trim($_POST['image_url'])) : 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80';

    $insert_sql = "INSERT INTO items (owner_id, category_id, title, description, daily_rate, security_deposit, item_condition, campus_spot, image_url, is_available) 
                   VALUES ($owner_id, $category_id, '$title', '$description', $daily_rate, $security_deposit, '$condition', '$pickup_spot', '$image_url', 1)";

    if (mysqli_query($conn, $insert_sql)) {
        header("Location: owner-dashboard.php?msg=item_added");
        exit();
    } else {
        die("Error adding equipment: " . mysqli_error($conn));
    }
} else {
    header("Location: owner-dashboard.php");
    exit();
}
?>
