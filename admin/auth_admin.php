<?php
/**
 * Require a logged-in admin (dummy admin session or users.role = admin).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isAdmin = (isset($_SESSION['user']) && $_SESSION['user'] === 'admin')
    || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

if (!$isAdmin) {
    header('Location: ../login.php');
    exit;
}
