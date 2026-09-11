<?php
session_start();
require_once('DBconnect.php');

// Ensure user is signed in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php?msg=login_required");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $item_id = (int)$_POST['item_id'];
    $renter_id = (int)$_SESSION['user_id'];
    $start_date = mysqli_real_escape_string($conn, trim($_POST['rental_start_date']));
    $end_date = mysqli_real_escape_string($conn, trim($_POST['rental_end_date']));
    $pickup_spot = mysqli_real_escape_string($conn, trim($_POST['pickup_spot']));

    // Query item pricing
    $item_sql = "SELECT daily_rate, security_deposit FROM items WHERE item_id = $item_id";
    $item_res = mysqli_query($conn, $item_sql);

    if ($item_res && mysqli_num_rows($item_res) > 0) {
        $item = mysqli_fetch_assoc($item_res);

        // Calculate rental days
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $diff_days = max(1, round(($end_ts - $start_ts) / 86400));

        $total_rent = $diff_days * $item['daily_rate'];
        $deposit = $item['security_deposit'];

        // Generate 6-8 character handover token (e.g. TRX-4821)
        $handover_token = 'TRX-' . rand(1000, 9999);

        // Insert into rental_requests table
        $insert_req_sql = "INSERT INTO rental_requests (item_id, renter_id, start_date, end_date, total_rent, deposit, handover_token, pickup_spot, status) 
                           VALUES ($item_id, $renter_id, '$start_date', '$end_date', $total_rent, $deposit, '$handover_token', '$pickup_spot', 'Pending')";
        
        if (mysqli_query($conn, $insert_req_sql)) {
            $request_id = mysqli_insert_id($conn);
            $trx_id = 'BK' . rand(10000000, 99999999) . 'X';
            $escrow_amount = $total_rent + $deposit;

            // Record escrow payment
            $insert_pay_sql = "INSERT INTO payments (request_id, method, amount, trx_id, status) 
                              VALUES ($request_id, 'bKash', $escrow_amount, '$trx_id', 'Escrow Locked')";
            mysqli_query($conn, $insert_pay_sql);

            header("Location: renter-dashboard.php?msg=requested&token=" . urlencode($handover_token));
            exit();
        } else {
            die("Error inserting rental request: " . mysqli_error($conn));
        }
    } else {
        die("Invalid item requested.");
    }
} else {
    header("Location: index.php");
    exit();
}
?>
