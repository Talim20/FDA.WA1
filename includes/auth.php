<?php
/**
 * Authentication Check
 * IMSTS - Integrated Market Surveillance Tracking System
 */

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Check session timeout (30 minutes)
$timeout = 30 * 60;
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout)) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

$_SESSION['login_time'] = time();

function hasRole($allowedRoles) {
    if (!isset($_SESSION['role'])) return false;
    return in_array($_SESSION['role'], $allowedRoles);
}

function requireRole($allowedRoles) {
    if (!hasRole($allowedRoles)) {
        $_SESSION['error'] = 'You do not have permission to access this page.';
        header('Location: dashboard.php');
        exit;
    }
}

function isAdmin() {
    return $_SESSION['role'] === 'administrator';
}

function isSupervisorOrAdmin() {
    return in_array($_SESSION['role'], ['supervisor', 'administrator']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? 0;
}

function getCurrentUserName() {
    return $_SESSION['full_name'] ?? 'Unknown';
}
?>
