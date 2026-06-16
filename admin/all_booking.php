<?php
// Include the database connection
include '../database/db_connect.php';

try {
    // Query to fetch all bookings
    $allBookingsQuery = "SELECT name, email, phone, address, location, guests, arrivals, leaving, package, price, created_at FROM bookings ORDER BY created_at DESC";
    
    // Execute query
    $allBookingsResult = $pdo->query($allBookingsQuery);

    // Fetch data
    if ($allBookingsResult) {
        $allBookings = $allBookingsResult->fetchAll(PDO::FETCH_ASSOC);
    } else {
        die("Error fetching data: " . $pdo->errorInfo());
    }
} catch (PDOException $e) {
    error_log("Error fetching data: " . $e->getMessage());
    die("Error fetching data. Please try again later.");
}
?>

<?php include 'component/nav_admin.php'; ?>

        <div class="main-content flex-grow-1 p-4">
     
        <div class="header mb-4">
                <h1 class="h3">All Bookings</h1>
            </div>
<!-- All Bookings -->
<div class="section mt-5">
              
                <table class="table table-striped mt-3">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Address</th>
                            <th scope="col">Location</th>
                            <th scope="col">Guests</th>
                            <th scope="col">Arrival Date</th>
                            <th scope="col">Leaving Date</th>
                            <th scope="col">Package</th>
                            <th scope="col">Price</th>
                            <th scope="col">Booked On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allBookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                                <td><?php echo htmlspecialchars($booking['address']); ?></td>
                                <td><?php echo htmlspecialchars($booking['location']); ?></td>
                                <td><?php echo htmlspecialchars($booking['guests']); ?></td>
                                <td><?php echo htmlspecialchars($booking['arrivals']); ?></td>
                                <td><?php echo htmlspecialchars($booking['leaving']); ?></td>
                                <td><?php echo htmlspecialchars($booking['package']); ?></td>
                                <td>$<?php echo number_format($booking['price'], 2); ?></td>
                                <td><?php echo htmlspecialchars($booking['created_at']); ?></td>
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