<?php
include '../../database/db_connect.php';

if (!isset($_GET['id'])) {
    die("Package ID not provided.");
}

$id = $_GET['id'];

try {
    $sql = "DELETE FROM packages WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    header("Location: update-package.php"); // Redirect to the list page
    exit;
} catch (PDOException $e) {
    die("Error deleting package: " . $e->getMessage());
}
?>
