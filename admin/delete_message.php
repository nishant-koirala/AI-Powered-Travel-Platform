<?php
require_once __DIR__ . '/auth_admin.php';
include __DIR__ . '/../database/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: messages.php');
    exit();
}

if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    $_SESSION['error_message'] = "Invalid request.";
    header('Location: messages.php');
    exit();
}

$message_id = (int) ($_POST['message_id'] ?? 0);
if ($message_id <= 0) {
    header('Location: messages.php');
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
    $stmt->execute(['id' => $message_id]);
    $_SESSION['success_message'] = "Message deleted successfully.";
} catch (PDOException $e) {
    error_log("delete_message: " . $e->getMessage());
    $_SESSION['error_message'] = "Failed to delete message.";
}

header('Location: messages.php');
exit();
?>
    