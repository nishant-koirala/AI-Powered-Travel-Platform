<?php
// login_process.php

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email    = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    $_SESSION['error_message'] = "Email and password are required.";
    header('Location: login.php');
    exit();
}

include 'database/db_connect.php';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user']     = $user['role'] === 'admin' ? 'admin' : 'customer';
        $_SESSION['user_id']  = (int) $user['id'];
        $_SESSION['username'] = $user['name'];
        $_SESSION['role']     = $user['role'];

        header($user['role'] === 'admin' ? 'Location: admin/admin.php' : 'Location: package.php');
        exit();
    }

    $_SESSION['error_message'] = "Invalid email or password. Please try again.";
    header('Location: login.php');
    exit();

} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred. Please try again later.";
    header('Location: login.php');
    exit();
}
?>
