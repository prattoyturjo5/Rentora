<?php
session_start();

$role = $_SESSION['role'] ?? 'member';

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

if ($role === 'admin') {
    header("Location: ../admin/login.php?logged_out=1");
} else {
    header("Location: login.php?logged_out=1");
}
exit();
