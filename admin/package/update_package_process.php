<?php
include('../../database/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageName = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "../images/" . $imageName);
    } else {
        $imageName = $_POST['current_image']; // Keep existing image if no new one is uploaded
    }

    try {
        $sql = "UPDATE packages SET title = :title, price = :price, description = :description, image = :image WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image', $imageName);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        header("Location: update-package.php?message=Package updated successfully&messageType=success");
        exit;
    } catch (PDOException $e) {
        die("Error updating package: " . $e->getMessage());
    }
}
