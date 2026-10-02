<?php
/**
 * Logout Script
 * IMSTS - Integrated Market Surveillance Tracking System
 */

session_start();

if (isset($_SESSION['user_id'])) {
    require_once 'config/database.php';
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $logSql = "INSERT INTO audit_log (user_id, action, description, ip_address) 
              VALUES (?, 'LOGOUT', 'User logged out', ?)";
    executeQuery($logSql, [$_SESSION['user_id'], $ip]);
}

$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}
session_destroy();

header('Location: login.php');
exit;
?>
