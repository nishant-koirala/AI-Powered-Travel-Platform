<?php
require_once __DIR__ . '/auth_admin.php';
include __DIR__ . '/../database/db_connect.php';

// SQLite uses strftime(); MySQL uses DATE_FORMAT()
$monthExpr = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite')
    ? "strftime('%Y-%m', arrivals)"
    : "DATE_FORMAT(arrivals, '%Y-%m')";

function getTotalBookings($pdo) {
    return $pdo->query("SELECT COUNT(*) as total FROM bookings")->fetch(PDO::FETCH_ASSOC)['total'];
}

function getTotalRevenue($pdo) {
    return $pdo->query("SELECT SUM(price) as total_revenue FROM bookings")->fetch(PDO::FETCH_ASSOC)['total_revenue'];
}

function getTotalCustomers($pdo) {
    return $pdo->query("SELECT COUNT(DISTINCT email) as total_customers FROM bookings")->fetch(PDO::FETCH_ASSOC)['total_customers'];
}

function getTotalPackages($pdo) {
    return $pdo->query("SELECT COUNT(DISTINCT package) as total_packages FROM bookings")->fetch(PDO::FETCH_ASSOC)['total_packages'];
}

function getCustomerGrowthData($pdo, $monthExpr) {
    $stmt = $pdo->prepare("SELECT $monthExpr AS month, COUNT(*) as count FROM bookings GROUP BY month ORDER BY month");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTopPackagesData($pdo) {
    $stmt = $pdo->prepare("SELECT package AS package_name, SUM(price) as revenue FROM bookings GROUP BY package ORDER BY revenue DESC LIMIT 5");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSalesData($pdo, $monthExpr) {
    $stmt = $pdo->prepare("SELECT $monthExpr AS month, SUM(price) as sales FROM bookings GROUP BY month ORDER BY month");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBookingDistributionData($pdo) {
    $stmt = $pdo->prepare("SELECT package AS package_name, COUNT(*) as count FROM bookings GROUP BY package");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMessages($pdo) {
    return $pdo->query("SELECT * FROM messages ORDER BY submitted_at DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch everything once
$totalBookings           = getTotalBookings($pdo);
$totalRevenue            = getTotalRevenue($pdo);
$totalCustomers          = getTotalCustomers($pdo);
$totalPackages           = getTotalPackages($pdo);
$customerGrowthData      = getCustomerGrowthData($pdo, $monthExpr);
$topPackagesData         = getTopPackagesData($pdo);
$salesData               = getSalesData($pdo, $monthExpr);
$bookingDistributionData = getBookingDistributionData($pdo);
$messages                = getMessages($pdo);

$customerGrowthJson      = json_encode($customerGrowthData);
$topPackagesJson         = json_encode($topPackagesData);
$salesJson               = json_encode($salesData);
$bookingDistributionJson = json_encode($bookingDistributionData);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* General Styles */
html, body {
    margin: 0;
    padding: 0;
    height: 100%;
    font-family: Arial, sans-serif;
}

.container {
    display: flex;
    height: 100vh;
}

.sidebar {
    width: 250px;
    height: 100vh;
    background-color: #343a40; /* Dark background for sidebar */
    color: white;
    position: fixed; /* Fixes the sidebar to the left */
    top: 0;
    left: 0;
    overflow-y: auto; /* Scroll if content overflows */
}

.main-content {
    margin-left: 250px; /* Space for the sidebar */
    padding: 20px;
    flex: 1; /* Takes the remaining space */
    background-color: #f8f9fa; /* Light background for main content */
    height: 100vh;
    overflow-y: auto; /* Scroll if content overflows */
}

/* Card Styles */
.card {
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    margin-bottom: 1rem;
}

.card-header {
    background-color: #007bff;
    color: white;
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #dee2e6;
}

.card-body {
    padding: 1.25rem;
}

.card-title {
    margin: 0;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .sidebar {
        width: 200px; /* Adjust width for smaller screens */
    }

    .main-content {
        margin-left: 200px; /* Adjust margin for smaller screens */
    }
}

.direct-messages {
    margin-top: 20px;
}

.direct-messages .card-header {
    background-color: #007bff;
    color: #fff;
    font-weight: bold;
}

.direct-messages .card-body {
    padding: 20px;
}

.direct-messages .form-control {
    border-radius: 0.25rem;
}

.direct-messages .btn-primary {
    border-radius: 0.25rem;
}

.direct-messages .list-group-item {
    border: 1px solid #ddd;
    margin-bottom: 10px;
    border-radius: 0.25rem;
    padding: 15px;
    background-color: #f8f9fa;
}

.direct-messages .list-group-item strong {
    display: block;
    margin-bottom: 5px;
}

.direct-messages .list-group-item p {
    margin: 0;
    font-size: 1rem;
}

.direct-messages .list-group-item small {
    display: block;
    margin-top: 10px;
    color: #6c757d;
}

.direct-messages .list-group-item:nth-child(odd) {
    background-color: #e9ecef;
}

.direct-messages .list-group-item:nth-child(even) {
    background-color: #f8f9fa;
}

.card-footer {
    padding: 10px;
    background-color: #f9f9f9;
    border-top: 1px solid #ddd;
}

.input-group {
    display: flex;
    align-items: center;
}

.input-group .form-control {
    border-radius: 0;
    padding: 10px;
    font-size: 14px;
}

.input-group .btn {
    background-color: #007bff;
    color: #fff;
    border-radius: 0;
    padding: 10px 20px;
}

/* Scrollbar customization */
.direct-chat-messages::-webkit-scrollbar {
    width: 6px;
}

.direct-chat-messages::-webkit-scrollbar-thumb {
    background-color: #007bff;
    border-radius: 10px;
}

    </style>
</head>
<body>
    <?php include 'component/nav_admin.php'; ?>

 <!-- Main Content -->
 <div class="main-content">
        <!-- Header -->
        <div class="header mb-4">
            <h1 class="h3">Reports</h1>
        </div>

        <!-- Summary Cards -->
        <div class="row g-3 mb-4">
            <!-- Total Bookings Card -->
            <div class="col-md-3">
                <div class="card text-center bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Total Bookings</h5>
                        <p class="card-text"><?php echo $totalBookings; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Revenue Card -->
            <div class="col-md-3">
                <div class="card text-center bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Total Revenue</h5>
                        <p class="card-text">$<?php echo number_format($totalRevenue, 2); ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Customers Card -->
            <div class="col-md-3">
                <div class="card text-center bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Total Customers</h5>
                        <p class="card-text"><?php echo $totalCustomers; ?></p>
                    </div>
                </div>
            </div>

            <!-- Total Packages Card -->
            <div class="col-md-3">
                <div class="card text-center bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Total Packages</h5>
                        <p class="card-text"><?php echo $totalPackages; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Charts Section -->
        <div class="row g-3">
            <!-- Customer Growth Over Time Chart -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Customer Growth Over Time</div>
                    <div class="card-body">
                        <canvas id="customerGrowthChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top 5 Packages by Revenue -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Top 5 Packages by Revenue</div>
                    <div class="card-body">
                        <canvas id="topPackagesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Reports Content -->
        <div class="row g-3">
            <!-- Sales Chart -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Total Sales Over Time</div>
                    <div class="card-body">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Booking Distribution Chart -->
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="card-header">Booking Distribution by Package</div>
                    <div class="card-body">
                        <canvas id="bookingDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

 

    <!-- Scripts -->
    <script>
        var customerGrowthData = <?php echo $customerGrowthJson; ?>;
        var ctx1 = document.getElementById('customerGrowthChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: customerGrowthData.map(item => item.month),
                datasets: [{
                    label: 'Customer Growth',
                    data: customerGrowthData.map(item => item.count),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true
                }]
            }
        });

        var topPackagesData = <?php echo $topPackagesJson; ?>;
        var ctx2 = document.getElementById('topPackagesChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: topPackagesData.map(item => item.package_name),
                datasets: [{
                    label: 'Revenue',
                    data: topPackagesData.map(item => item.revenue),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            }
        });

        var salesData = <?php echo $salesJson; ?>;
        var ctx3 = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx3, {
            type: 'line',
            data: {
                labels: salesData.map(item => item.month),
                datasets: [{
                    label: 'Sales',
                    data: salesData.map(item => item.sales),
                    borderColor: 'rgba(255, 159, 64, 1)',
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    fill: true
                }]
            }
        });

        var bookingDistributionData = <?php echo $bookingDistributionJson; ?>;
        var ctx4 = document.getElementById('bookingDistributionChart').getContext('2d');
        new Chart(ctx4, {
            type: 'doughnut',
            data: {
                labels: bookingDistributionData.map(item => item.package_name),
                datasets: [{
                    label: 'Booking Distribution',
                    data: bookingDistributionData.map(item => item.count),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    </script>
</body>
</html>