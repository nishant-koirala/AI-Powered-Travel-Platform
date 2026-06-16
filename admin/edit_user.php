<?php
// Include the database connection
include '../database/db_connect.php';

try {
    // Fetch user data if ID is provided
    if (isset($_GET['id'])) {
        $userId = intval($_GET['id']);
        $userQuery = "SELECT * FROM users WHERE id = :id";
        $stmt = $pdo->prepare($userQuery);
        $stmt->execute(['id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        die("No user ID provided.");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = $_POST['name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $role = $_POST['role'];
        
        // Validate input
        if (empty($name) || empty($email) || empty($phone) || empty($role)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Please enter a valid email address.';
            $messageType = 'danger';
        } else {
            // Update user details
            $updateQuery = "UPDATE users SET name = :name, email = :email, phone = :phone, role = :role WHERE id = :id";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'id' => $userId
            ]);
            
            // Success message
            $message = 'User updated successfully.';
            $messageType = 'success';

            // Redirect to the users page
            header("Refresh:2; url=users.php");
        }
    }
} catch (PDOException $e) {
    error_log("Error updating user: " . $e->getMessage());
    $message = 'Error updating user. Please try again later.';
    $messageType = 'danger';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="h4">Edit User</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form action="edit_user.php?id=<?php echo $userId; ?>" method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <input type="text" class="form-control" id="role" name="role" value="<?php echo htmlspecialchars($user['role']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
