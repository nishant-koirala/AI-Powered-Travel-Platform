<?php
// Include the database connection
include '../database/db_connect.php';

try {
    // Fetch data from the database
    $totalBookingsQuery = "SELECT COUNT(*) AS totalBookings FROM bookings";
    $newSignupsQuery = "SELECT COUNT(*) AS newSignups FROM bookings WHERE DATE(created_at) = CURDATE()";
    $salesQuery = "SELECT SUM(price) AS totalSales FROM bookings";
    $recentActivitiesQuery = "SELECT name, package, arrivals, leaving FROM bookings ORDER BY created_at DESC LIMIT 5";
    $allBookingsQuery = "SELECT name, email, phone, address, location, guests, arrivals, leaving, package, price, created_at FROM bookings ORDER BY created_at DESC";

    // Execute queries
    $totalBookingsResult = $pdo->query($totalBookingsQuery);
    $newSignupsResult = $pdo->query($newSignupsQuery);
    $salesResult = $pdo->query($salesQuery);
    $recentActivitiesResult = $pdo->query($recentActivitiesQuery);
    $allBookingsResult = $pdo->query($allBookingsQuery);

    // Check if queries are successful
    if ($totalBookingsResult && $newSignupsResult && $salesResult && $recentActivitiesResult && $allBookingsResult) {
        // Fetch data as associative arrays
        $totalBookings = $totalBookingsResult->fetch(PDO::FETCH_ASSOC)['totalBookings'];
        $newSignups = $newSignupsResult->fetch(PDO::FETCH_ASSOC)['newSignups'];
        $totalSales = $salesResult->fetch(PDO::FETCH_ASSOC)['totalSales'];
        $recentActivities = $recentActivitiesResult->fetchAll(PDO::FETCH_ASSOC);
        $allBookings = $allBookingsResult->fetchAll(PDO::FETCH_ASSOC);
    } else {
        die("Error fetching data: " . $pdo->errorInfo());
    }
} catch (PDOException $e) {
    // Log the error (optional)
    error_log("Error fetching data: " . $e->getMessage());
    die("Error fetching data. Please try again later.");
}
?>
<?php include 'component/nav_admin.php'; ?>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <!-- Header -->
            <div class="header mb-4">
                <h1 class="h3">Admin Dashboard</h1>
            </div>

            <!-- Dashboard Content -->
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Total Bookings</div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($totalBookings); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-header">New Signups</div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($newSignups); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-header">Sales</div>
                        <div class="card-body">
                            <h5 class="card-title">$<?php echo number_format($totalSales, 2); ?></h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger mb-3">
                        <div class="card-header">Active Sessions</div>
                        <div class="card-body">
                            <h5 class="card-title">N/A</h5> <!-- Update this if you have session tracking -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="section mt-5">
                <h2 class="h4">Recent Bookings</h2>
                <table class="table table-hover mt-3">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">User</th>
                            <th scope="col">Package</th>
                            <th scope="col">Arrival Date</th>
                            <th scope="col">Leaving Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                <td><?php echo htmlspecialchars($activity['package']); ?></td>
                                <td><?php echo htmlspecialchars($activity['arrivals']); ?></td>
                                <td><?php echo htmlspecialchars($activity['leaving']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

          
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/admin.js" defer></script>
</body>
</html>
