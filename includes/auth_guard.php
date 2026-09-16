<?php
/**
 * Authentication and Access Control Guards
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Enforce Admin Role Guard
 * Redirects to admin login if not authenticated as admin
 */
function require_admin($loginPath = '../admin/login.php') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: $loginPath");
        exit();
    }
}

/**
 * Enforce Member Role Guard
 * Redirects to member login if not authenticated as member
 */
function require_member($loginPath = '../auth/login.php') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'member' || !isset($_SESSION['user_id'])) {
        header("Location: $loginPath");
        exit();
    }
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['role']) && isset($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if current user is member
 */
function is_member() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'member';
}
