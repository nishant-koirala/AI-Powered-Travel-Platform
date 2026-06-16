
<?php
// Include the database connection
$filePath = '../../database/db_connect.php';
if (file_exists($filePath)) {
    include($filePath);
} else {
    die("Error: Database connection file not found at $filePath");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $price = $_POST['price'] ?? '';

    // Check if an image file was uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = '../../images/'; // Ensure this directory exists and is writable
        $imagePath = $uploadDir . $imageName;

        // Validate file type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedExtensions)) {
            $error = "Invalid image file type. Only JPG, JPEG, PNG, and GIF are allowed.";
        } else {
            // Move the uploaded file to the images directory
            if (move_uploaded_file($imageTmpPath, $imagePath)) {
                // Save the relative path to the database
                $imageRelativePath =  $imageName;

                // Insert package into the database
                if (isset($pdo)) {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO packages (title, description, price, image) VALUES (:title, :description, :price, :image)");
                        $stmt->execute([
                            'title' => $title,
                            'description' => $description,
                            'price' => $price,
                            'image' => $imageRelativePath,
                        ]);
                        $success = "Package added successfully!";
                    } catch (PDOException $e) {
                        $error = "Failed to add package: " . $e->getMessage();
                    }
                } else {
                    $error = "Database connection not initialized.";
                }
            } else {
                $error = "Failed to upload the image.";
            }
        }
    } else {
        $error = "Please upload a valid image.";
    }
}
?>

<?php include '../component/nav_admin.php'; ?>

<div class="main-content flex-grow-1 p-4">
    <div class="header mb-4">
        <h1 class="h3">Add New Package</h1>
    </div>

    <!-- Success/Error Messages -->
    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    <style>
    .sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 250px !important;
    height: 100vh !important; /* Full viewport height */
    overflow-y: auto;
    background-color: #343a40; /* Dark background for sidebar */
    color: #ffffff; /* White text color */
}

/* Ensure the main content takes the remaining space */
.main-content {
    margin-left: 250px !important;
    width: calc(100% - 250px) !important; /* Full width minus the sidebar width */
    min-height: 100vh; /* Ensure it takes at least the full viewport height */
}
</style>
    <!-- Form for Adding Package -->
    <form method="post" action="add_package.php" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label for="price">Price</label>
            <input type="number" name="price" id="price" class="form-control" step="0.01" required>
        </div>

        <div class="form-group">
            <label for="image">Image</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Package</button>
    </form>
</div>
