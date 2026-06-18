<?php
require_once 'auth_admin.php';
include '../database/db_connect.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

try {
    // Query to fetch user data
    $usersQuery = "SELECT id, name, email, phone, role FROM users ORDER BY created_at DESC LIMIT 200";
    
    // Execute query
    $usersResult = $pdo->query($usersQuery);

    $users = $usersResult->fetchAll(PDO::FETCH_ASSOC);

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
                            <a href="edit_user.php?id=<?php echo (int)$user['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                            <form method="POST" action="delete_user.php" style="display:inline" onsubmit="return confirm('Delete this user?')">
                                <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
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
