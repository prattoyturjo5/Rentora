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

$lender_id = intval($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw_token = $_POST['token_input'] ?? $_POST['handover_token'] ?? '';
    $token_input = strtoupper(trim($raw_token));

    if (empty($token_input)) {
        header("Location: rentals.php?verify_status=invalid");
        exit();
    }

    $token = mysqli_real_escape_string($conn, $token_input);

    $sql = "SELECT r.*, e.owner_id 
            FROM rental_agreement r 
            JOIN equipment e ON r.equipment_id = e.equipment_id 
            WHERE UPPER(TRIM(r.handover_token)) = '$token' 
              AND e.owner_id = '$lender_id' 
              AND r.status != 'Active' 
              AND r.status != 'Completed' 
              AND r.status != 'Cancelled'
            LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        $agreement_id = intval($row['rental_id'] ?? $row['agreement_id'] ?? 0);
        $renter_id = intval($row['renter_id']);
        $equipment_id = intval($row['equipment_id']);

        // 1. Activate rental agreement
        mysqli_query($conn, "UPDATE rental_agreement SET status = 'Active' WHERE rental_id = '$agreement_id'");
        mysqli_query($conn, "UPDATE equipment SET availability_status = 'Rented' WHERE equipment_id = '$equipment_id'");

        // 2. Mark both Lender and Renter as Verified in member table
        mysqli_query($conn, "UPDATE member SET status = 'Verified' WHERE member_id IN ('$lender_id', '$renter_id')");

        // 3. Update active session status for current user
        $_SESSION['status'] = 'Verified';

        header("Location: rentals.php?verify_status=success");
        exit();
    } else {
        header("Location: rentals.php?verify_status=invalid");
        exit();
    }
}

header("Location: rentals.php");
exit();
