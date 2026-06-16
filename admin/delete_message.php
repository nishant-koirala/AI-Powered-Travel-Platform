<?php
session_start();
include '../database/db_connect.php'; // Include your database connection file

// Check if the user is an admin
// if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
//     header('Location: login.php');
//     exit();
// }

// Check if message_id is set
if (isset($_POST['message_id'])) {
    $message_id = (int)$_POST['message_id'];

    try {
        // Prepare and execute the delete statement
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = :id");
        $stmt->execute(['id' => $message_id]);

        // Redirect back to the messages page with a success message
        $_SESSION['success_message'] = "Message deleted successfully.";
    } catch (PDOException $e) {
        // Handle any errors that occur
        $_SESSION['error_message'] = "Failed to delete message: " . $e->getMessage();
    }

    header('Location: recivedMessage.php'); // Adjust the redirect URL as needed
    exit();
} else {
    // If no message_id is provided, redirect to the messages page
    header('Location: recivedMessage.php');
    exit();
}
?>
    