<?php
// Include the database connection
include '../database/db_connect.php';

try {
    // Query to fetch user data
    $usersQuery = "SELECT id, name, email, phone, role FROM users ORDER BY created_at DESC";
    
    // Execute query
    $usersResult = $pdo->query($usersQuery);

    // Fetch data
    if ($usersResult) {
        $users = $usersResult->fetchAll(PDO::FETCH_ASSOC);
    } else {
        die("Error fetching data: " . $pdo->errorInfo());
    }

    // Message handling
    $message = '';
    $messageType = '';

    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message']);
        $messageType = htmlspecialchars($_GET['messageType']);
    }
} catch (PDOException $e) {
    error_log("Error fetching data: " . $e->getMessage());
    die("Error fetching data. Please try again later.");
}
?>

<?php include 'component/nav_admin.php'; ?>

<!-- Main Content -->
<div class="main-content flex-grow-1 p-4">
    <!-- Header -->
    <div class="header mb-4">
        <h1 class="h3">Users</h1>
    </div>

    <!-- Users Section -->
    <div class="section mt-5">
        <h2 class="h4">User List</h2>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($messageType); ?>" role="alert">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <table class="table table-striped mt-3">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Role</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="js/admin.js" defer></script>
</body>
</html>
