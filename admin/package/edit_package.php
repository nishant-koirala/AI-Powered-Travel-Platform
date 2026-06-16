<?php
include('../../database/db_connect.php');

if (!isset($_GET['id'])) {
    die("Package ID not provided.");
}

$id = $_GET['id'];

try {
    $sql = "SELECT * FROM packages WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$package) {
        die("Package not found.");
    }
} catch (PDOException $e) {
    die("Error fetching package details: " . $e->getMessage());
}

 include '../component/nav_admin.php';
?>

<div class="main-content flex-grow-1 p-4">
    <div class="header mb-4">
        <h1 class="h3">Edit Package</h1>
    </div>

    <form action="update_package_process.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $package['id']; ?>">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($package['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" name="price" id="price" class="form-control" value="<?php echo htmlspecialchars($package['price']); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control"><?php echo htmlspecialchars($package['description']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="image">Image</label>
            <input type="file" name="image" id="image" class="form-control">
            <small>Current image: <?php echo htmlspecialchars($package['image']); ?></small>
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($package['image']); ?>">
        </div>

        <button type="submit" class="btn btn-primary">Update Package</button>
    </form>
</div>
