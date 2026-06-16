<?php
include '../../database/db_connect.php';

try {
    $sql = "SELECT * FROM packages ORDER BY id DESC";
    $stmt = $pdo->query($sql);
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

include '../component/nav_admin.php';
?>
  <style>
        /* Sidebar and Content Styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            height: 100vh;
            background-color: #343a40;
            color: white;
            overflow-y: auto;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            width: calc(100% - 250px);
        }
        </style>
<div class="main-content flex-grow-1 p-4">
    <div class="header mb-4">
        <h1 class="h3">Package List</h1>
    </div>

    <a href="add_package.php" class="btn btn-success mb-3">Add New Package</a>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Title</th>
                <th scope="col">Price</th>
                <th scope="col">Description</th>
                <th scope="col">Image</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?php echo $package['id']; ?></td>
                    <td><?php echo htmlspecialchars($package['title']); ?></td>
                    <td><?php echo htmlspecialchars($package['price']); ?></td>
                    <td><?php echo htmlspecialchars($package['description']); ?></td>
                    <td><img src="../../images/<?php echo htmlspecialchars($package['image']); ?>" alt="<?php echo htmlspecialchars($package['title']); ?>" width="50"></td>
                    <td>
                        <a href="edit_package.php?id=<?php echo $package['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                        <a href="#" onclick="confirmDelete(<?php echo $package['id']; ?>)" class="btn btn-danger btn-sm">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function confirmDelete(id) {
        if (confirm('Are you sure you want to delete this package?')) {
            window.location.href = 'delete_package.php?id=' + id;
        }
    }
</script>
