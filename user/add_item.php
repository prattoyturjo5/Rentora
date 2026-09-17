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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['item_title']) || isset($_POST['title']) || isset($_POST['equipment_name']))) {
    $owner_id = (int)($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
    $equipment_name = trim($_POST['equipment_name'] ?? $_POST['item_title'] ?? $_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $condition = trim($_POST['condition_status'] ?? $_POST['item_condition'] ?? 'Good');
    if ($condition === 'Like New') {
        $condition = 'New';
    }
    if (!in_array($condition, ['New', 'Good', 'Fair', 'Poor'])) {
        $condition = 'Good';
    }
    $rental_rate = floatval($_POST['rental_rate'] ?? $_POST['daily_rate'] ?? 0);
    $security_deposit = floatval($_POST['security_deposit'] ?? $_POST['deposit'] ?? 0);
    $campus_spot = trim($_POST['campus_spot'] ?? $_POST['pickup_spot'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');
    $description = trim($_POST['item_description'] ?? $_POST['description'] ?? '');

    if (empty($equipment_name) || $rental_rate <= 0) {
        header("Location: equipment.php?error=missing_fields");
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO equipment (owner_id, category_id, equipment_name, description, rental_rate, security_deposit, campus_spot, image_url, condition_status, availability_status) 
            VALUES (:owner_id, :category_id, :equipment_name, :description, :rental_rate, :security_deposit, :campus_spot, :image_url, :condition_status, 'Available')
        ");
        $stmt->execute([
            'owner_id'         => $owner_id,
            'category_id'      => $category_id,
            'equipment_name'   => $equipment_name,
            'description'      => $description,
            'rental_rate'      => $rental_rate,
            'security_deposit' => $security_deposit,
            'campus_spot'      => $campus_spot,
            'image_url'        => $image_url,
            'condition_status' => $condition
        ]);

        header("Location: equipment.php?msg=item_added");
        exit();
    } catch (PDOException $e) {
        die("Error adding equipment: " . htmlspecialchars($e->getMessage()));
    }

} else {
    header("Location: equipment.php");
    exit();
}
