<?php
// Include the database connection
include '../database/db_connect.php';

try {
    // Check if ID is provided
    if (isset($_GET['id'])) {
        $userId = intval($_GET['id']);
        
        // Delete query
        $deleteQuery = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($deleteQuery);
        $stmt->execute(['id' => $userId]);

        // Redirect with success message
        header("Location: users.php?message=User deleted successfully&messageType=success");
    } else {
        die("No user ID provided.");
    }
} catch (PDOException $e) {
    error_log("Error deleting user: " . $e->getMessage());
    header("Location: users.php?message=Error deleting user&messageType=danger");
}
?>
