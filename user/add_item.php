<?php
session_start();
require_once(__DIR__ . '/../config/db.php');
require_once(__DIR__ . '/../includes/auth_guard.php');

require_member('../auth/login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['item_title']) || isset($_POST['title']))) {
    $owner_id = (int)$_SESSION['user_id'];
    $title = trim($_POST['item_title'] ?? $_POST['title'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);
    $condition = trim($_POST['item_condition'] ?? 'Good');
    $pickup_spot = trim($_POST['pickup_spot'] ?? 'Central Library Front Gate');
    $daily_rate = floatval($_POST['daily_rate'] ?? 0);
    $security_deposit = floatval($_POST['security_deposit'] ?? 0);
    $description = trim($_POST['item_description'] ?? $_POST['description'] ?? '');
    $image_url = !empty($_POST['image_url']) ? trim($_POST['image_url']) : 'https://images.unsplash.com/photo-1581291518857-4e27b48ff24e?w=600&auto=format&fit=crop&q=80';

    if (empty($title) || $daily_rate <= 0) {
        header("Location: equipment.php?error=missing_fields");
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO equipment (member_id, category_id, title, description, daily_rate, security_deposit, item_condition, campus_spot, image_url, is_available) 
            VALUES (:member_id, :category_id, :title, :description, :daily_rate, :security_deposit, :condition, :spot, :image_url, 1)
        ");
        $stmt->execute([
            'member_id'        => $owner_id,
            'category_id'      => $category_id,
            'title'            => $title,
            'description'      => $description,
            'daily_rate'       => $daily_rate,
            'security_deposit' => $security_deposit,
            'condition'        => $condition,
            'spot'             => $pickup_spot,
            'image_url'        => $image_url
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
