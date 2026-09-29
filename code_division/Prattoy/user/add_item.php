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

$owner_id = (int)($_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0);
$member_status = get_member_status($pdo, $owner_id);

if ($member_status !== 'Verified') {
    $err = ($member_status === 'Rejected') ? 'account_rejected' : 'account_pending';
    header("Location: equipment.php?error=" . $err);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['item_title']) || isset($_POST['title']) || isset($_POST['equipment_name']) || isset($_POST['add_equipment']))) {
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
    $campus_spot = trim($_POST['campus_spot'] ?? $_POST['pickup_spot'] ?? 'Hazari Lane');
    $description = trim($_POST['item_description'] ?? $_POST['description'] ?? '');
    $lender_phone = trim($_POST['lender_phone'] ?? '');
    $lender_email = trim($_POST['lender_email'] ?? '');

    // Synchronize lender contact details to member profile without schema changes
    if (!empty($lender_phone) || !empty($lender_email)) {
        try {
            $upCols = [];
            $upParams = ['id' => $owner_id];
            if (!empty($lender_phone)) {
                $upCols[] = "phone_number = :phone";
                $upParams['phone'] = $lender_phone;
            }
            if (!empty($lender_email)) {
                $upCols[] = "university_email = :email";
                $upParams['email'] = $lender_email;
            }
            if (!empty($upCols)) {
                $upSql = "UPDATE member SET " . implode(', ', $upCols) . " WHERE member_id = :id";
                $upStmt = $pdo->prepare($upSql);
                $upStmt->execute($upParams);
            }
        } catch (Exception $e) {
            // Non-fatal if schema prevents update
        }
    }

    // 1. Text field validation
    if (empty($equipment_name) || $rental_rate <= 0) {
        header("Location: equipment.php?error=missing_fields#add-item-modal");
        exit();
    }

    // 2. File upload validation (mandatory server-side)
    $file = $_FILES['equipment_image'] ?? null;
    if (!$file || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        header("Location: equipment.php?error=missing_image#add-item-modal");
        exit();
    }

    // Check file size (max 5MB = 5 * 1024 * 1024 bytes)
    $max_size = 5 * 1024 * 1024;
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE || $file['size'] > $max_size) {
        header("Location: equipment.php?error=file_too_large#add-item-modal");
        exit();
    }

    if ($file['error'] !== UPLOAD_ERR_OK || empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        header("Location: equipment.php?error=upload_failed#add-item-modal");
        exit();
    }

    // Validate real file content / MIME type using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $real_mime = $finfo->file($file['tmp_name']);

    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowed_mimes[$real_mime])) {
        header("Location: equipment.php?error=invalid_image#add-item-modal");
        exit();
    }

    // Additional check: verify image integrity via getimagesize
    $image_info = @getimagesize($file['tmp_name']);
    if ($image_info === false) {
        header("Location: equipment.php?error=invalid_image#add-item-modal");
        exit();
    }
    $allowed_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
    if (!in_array($image_info[2], $allowed_types, true)) {
        header("Location: equipment.php?error=invalid_image#add-item-modal");
        exit();
    }

    // 3. Prepare upload directory
    $upload_dir = dirname(__DIR__) . '/uploads/equipment/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Determine extension from validated MIME type
    $ext = $allowed_mimes[$real_mime];
    if ($ext === 'jpg') {
        $orig_ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($orig_ext === 'jpeg') {
            $ext = 'jpeg';
        }
    }

    // Generate random/unique filename
    $unique_name = uniqid('eq_', true) . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $target_path = $upload_dir . $unique_name;

    if (!move_uploaded_file($file['tmp_name'], $target_path)) {
        header("Location: equipment.php?error=upload_failed#add-item-modal");
        exit();
    }

    // Relative path stored in database
    $db_image_path = 'uploads/equipment/' . $unique_name;

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
            'image_url'        => $db_image_path,
            'condition_status' => $condition
        ]);

        header("Location: equipment.php?msg=item_added");
        exit();
    } catch (PDOException $e) {
        // Clean up uploaded file if database insert fails
        if (file_exists($target_path)) {
            @unlink($target_path);
        }
        header("Location: equipment.php?error=db_error#add-item-modal");
        exit();
    }

} else {
    header("Location: equipment.php");
    exit();
}
