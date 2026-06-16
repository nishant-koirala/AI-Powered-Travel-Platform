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
.table-enhanced {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.table-responsive {
    overflow-x: auto;
}

.table-enhanced th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    border: none;
    padding: 12px 8px;
    text-align: left;
    white-space: nowrap;
}

.table-enhanced td {
    padding: 12px 8px;
    border-bottom: 1px solid #e9ecef;
    vertical-align: middle;
}

.table-enhanced tr:hover td {
    background-color: #f8f9fa;
}

.package-info {
    max-width: 200px;
}

.package-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
}

.package-price {
    color: #667eea;
    font-weight: 700;
    font-size: 1.1rem;
}

.package-meta {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 4px;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 2px;
}

.meta-badge {
    background: #e9ecef;
    padding: 2px 6px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-easy { background: #d4edda; color: #155724; }
.badge-moderate { background: #fff3cd; color: #856404; }
.badge-challenging { background: #f8d7da; color: #721c24; }

.btn-action {
    padding: 6px 12px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 0.85rem;
    font-weight: 500;
    margin-right: 4px;
    transition: all 0.3s ease;
}

.btn-edit {
    background: #ffc107;
    color: #212529;
}

.btn-edit:hover {
    background: #e0a800;
    color: white;
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-delete:hover {
    background: #c82333;
}

.btn-view {
    background: #667eea;
    color: white;
}

.btn-view:hover {
    background: #5a6fd8;
    color: white;
}

@media (max-width: 768px) {
    .package-info {
        max-width: 150px;
    }
    
    .table-enhanced th,
    .table-enhanced td {
        padding: 8px 4px;
        font-size: 0.85rem;
    }
}
</style>

<div class="main-content flex-grow-1 p-4">
    <div class="header mb-4">
        <h1 class="h3">📦 Enhanced Package Management</h1>
    </div>
    
    <a href="add_package.php" class="btn btn-success mb-3">➕ Add New Package</a>
    <a href="packages.php" class="btn btn-secondary mb-3">📋 Simple View</a>
    
    <div class="table-responsive mt-4">
        <table class="table table-striped table-enhanced">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Package Title</th>
                    <th scope="col">Price</th>
                    <th scope="col">Duration</th>
                    <th scope="col">Difficulty</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($packages as $package): ?>
                <tr>
                    <td><?php echo $package['id']; ?></td>
                    <td>
                        <div class="package-info">
                            <div class="package-title"><?php echo htmlspecialchars($package['title']); ?></div>
                            <div class="package-price">$<?php echo number_format($package['price'], 2); ?></div>
                            <div class="package-meta">
                                <div class="meta-item">
                                    <span class="meta-badge badge-<?php echo $package['difficulty_level'] ?? 'easy'; ?>">
                                        <?php echo ucfirst($package['difficulty_level'] ?? 'Easy'); ?>
                                    </span>
                                </div>
                                <?php if ($package['duration_days']): ?>
                                <div class="meta-item">
                                    📅 <?php echo $package['duration_days']; ?> days
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td>$<?php echo number_format($package['price'], 2); ?></td>
                    <td>
                        <?php if ($package['duration_days']): ?>
                        <span class="meta-badge badge-easy">📅 <?php echo $package['duration_days']; ?> days</span>
                        <?php else: ?>
                        <span class="meta-badge">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="meta-badge badge-<?php echo $package['difficulty_level'] ?? 'easy'; ?>">
                            <?php echo ucfirst($package['difficulty_level'] ?? 'Easy'); ?>
                        </span>
                    </td>
                    <td>
                        <a href="edit_package_enhanced.php?id=<?php echo $package['id']; ?>" class="btn-action btn-edit">✏️ Edit</a>
                        <a href="package_details.php?id=<?php echo $package['id']; ?>" class="btn-action btn-view" target="_blank">👁️ View</a>
                        <a href="delete_package.php?id=<?php echo $package['id']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this package?');">🗑️ Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (empty($packages)): ?>
    <div class="alert alert-info mt-4">
        <h4>No packages found</h4>
        <p>Start by adding your first package with enhanced features including duration, difficulty level, accommodation type, and detailed itineraries.</p>
    </div>
    <?php endif; ?>
</div>

<script>
// Add search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = '🔍 Search packages...';
    searchInput.className = 'form-control mb-3';
    searchInput.style.maxWidth = '300px';
    
    const header = document.querySelector('.header');
    header.appendChild(searchInput);
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            const title = row.querySelector('.package-title')?.textContent.toLowerCase() || '';
            if (title.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});
</script>
