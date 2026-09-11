<?php
session_start();
require_once('DBconnect.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php?msg=login_required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['handover_token'])) {
    $owner_id = (int)$_SESSION['user_id'];
    $token = mysqli_real_escape_string($conn, strtoupper(trim($_POST['handover_token'])));

    // Verify token exists for an item owned by this owner with status Approved
    $check_sql = "SELECT rental_requests.*, items.title, users.name as renter_name 
                  FROM rental_requests 
                  JOIN items ON rental_requests.item_id = items.item_id 
                  JOIN users ON rental_requests.renter_id = users.user_id 
                  WHERE rental_requests.handover_token = '$token' 
                  AND items.owner_id = $owner_id 
                  AND rental_requests.status IN ('Approved', 'Pending')";
    $check_res = mysqli_query($conn, $check_sql);

    if ($check_res && mysqli_num_rows($check_res) > 0) {
        $rental = mysqli_fetch_assoc($check_res);
        $request_id = $rental['request_id'];
        $item_id = $rental['item_id'];

        // Update rental request status to Active
        $update_req = "UPDATE rental_requests SET status = 'Active' WHERE request_id = $request_id";
        mysqli_query($conn, $update_req);

        // Mark item as unavailable during rental
        $update_item = "UPDATE items SET is_available = 0 WHERE item_id = $item_id";
        mysqli_query($conn, $update_item);

        header("Location: owner-dashboard.php?handover_status=success&renter=" . urlencode($rental['renter_name']) . "&item=" . urlencode($rental['title']));
        exit();
    } else {
        header("Location: owner-dashboard.php?handover_status=error&token=" . urlencode($token));
        exit();
    }
} else {
    header("Location: owner-dashboard.php");
    exit();
}
?>
