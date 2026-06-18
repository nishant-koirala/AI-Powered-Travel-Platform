<?php
// Include the database connection
include 'database/db_connect.php'; // Adjust the path as needed

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim(strip_tags($_POST['name']    ?? ''));
    $email   = trim($_POST['email']   ?? '');
    $phone   = trim(strip_tags($_POST['phone']   ?? ''));
    $message = trim(strip_tags($_POST['message'] ?? ''));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
        exit();
    }

    if ($name === '' || $message === '') {
        echo json_encode(['status' => 'error', 'message' => 'Name and message are required.']);
        exit();
    }

    $sql = "INSERT INTO messages (name, email, phone, message) VALUES (:name, :email, :phone, :message)";
    $stmt = $pdo->prepare($sql);

    // Bind parameters
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':message', $message);

    // Execute query and check if it was successful
    try {
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            $errorInfo = $stmt->errorInfo();
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit your message: ' . $errorInfo[2]]);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
