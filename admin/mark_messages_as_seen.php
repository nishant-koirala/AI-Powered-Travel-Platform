<?php
// Include the database connection
include '../database/db_connect.php';

try {
    // Update all unseen messages to seen
    $updateQuery = "UPDATE messages SET seen = 1 WHERE seen = 0";
    $pdo->exec($updateQuery);

    // Return a success response
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("Error updating messages: " . $e->getMessage());
    echo json_encode(['success' => false]);
}
?>
