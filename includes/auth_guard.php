<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_admin($loginPath = '../admin/login.php') {
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: $loginPath");
        exit();
    }
}

function require_member($loginPath = '../auth/login.php') {
    if (($_SESSION['role'] ?? '') !== 'member') {
        header("Location: $loginPath");
        exit();
    }
}

function is_logged_in() {
    return isset($_SESSION['role']) && (isset($_SESSION['member_id']) || isset($_SESSION['admin_id']) || isset($_SESSION['user_id']));
}

function is_admin() {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function is_member() {
    return ($_SESSION['role'] ?? '') === 'member';
}


function get_member_status($pdo, $member_id) {
    if (!$pdo || !$member_id) {
        return 'Pending';
    }
    try {
        $stmt = $pdo->prepare("SELECT status FROM member WHERE member_id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$member_id]);
        $status = $stmt->fetchColumn();
        return $status ? $status : 'Pending';
    } catch (Exception $e) {
        return 'Pending';
    }
}

/**
 * Enforce Verified Member Guard
 * Redirects if member is not verified
 */
function require_verified_member($pdo, $redirectPath = '../user/dashboard.php') {
    require_member();
    $member_id = $_SESSION['member_id'] ?? $_SESSION['user_id'] ?? 0;
    $status = get_member_status($pdo, $member_id);
    if ($status !== 'Verified') {
        $err = ($status === 'Rejected') ? 'account_rejected' : 'account_pending';
        $sep = (strpos($redirectPath, '?') !== false) ? '&' : '?';
        header("Location: " . $redirectPath . $sep . "error=" . $err);
        exit();
    }
    return $status;
}

