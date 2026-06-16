<?php
// API Usage Dashboard
include __DIR__ . '/auth_admin.php';
include __DIR__ . '/../database/db_connect.php';

// Get current quota
$quota = [];
try {
    $stmt = $pdo->query("SELECT api_call_limit, period_calls FROM chatbot_system_quota WHERE id = 1");
    $quota = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quota) {
        // Set default quota if not exists
        $pdo->exec("INSERT IGNORE INTO chatbot_system_quota (id, api_call_limit, period_calls) VALUES (1, 100, 0)");
        $stmt = $pdo->query("SELECT api_call_limit, period_calls FROM chatbot_system_quota WHERE id = 1");
        $quota = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("API Dashboard Error: " . $e->getMessage());
    $quota = ['api_call_limit' => 100, 'period_calls' => 0];
}

$limit = $quota['api_call_limit'] ?? 100;
$used = $quota['period_calls'] ?? 0;
$remaining = $limit - $used;
$percentage = $limit > 0 ? ($used / $limit) * 100 : 0;

// Calculate progress bar
$progressBlocks = 20;
$filledBlocks = floor(($percentage / 100) * $progressBlocks);
$emptyBlocks = $progressBlocks - $filledBlocks;

$progressBar = str_repeat('█', $filledBlocks) . str_repeat('░', $emptyBlocks);

include __DIR__ . '/component/nav_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Usage Dashboard - Travel Website Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .progress-container {
            background: #e9ecef;
            border-radius: 5px;
            padding: 3px;
            font-family: monospace;
            font-size: 14px;
            letter-spacing: 2px;
        }
        
        .usage-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .stat-item {
            text-align: center;
            flex: 1;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .plan-card {
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }
        
        .plan-card.recommended {
            border-color: #28a745;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .plan-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #007bff;
        }
        
        .btn-purchase {
            width: 100%;
            margin-top: 15px;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
    </style>
</head>
<body>
    <div class="main-content flex-grow-1 p-4">
        <div class="header mb-4">
            <h1 class="h3">📊 API Usage Overview</h1>
            <p class="text-muted">Monitor your chatbot API usage and purchase additional credits</p>
        </div>
        
        <!-- Usage Statistics -->
        <div class="dashboard-card">
            <h4 class="mb-4">📈 Usage Statistics</h4>
            
            <div class="usage-stat">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $limit; ?></div>
                    <div class="stat-label">Total Limit</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $used; ?></div>
                    <div class="stat-label">Used Calls</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $remaining; ?></div>
                    <div class="stat-label">Remaining</div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="mt-4">
                <h5>Usage Progress</h5>
                <div class="progress-container">
                    [<?php echo $progressBar; ?>] <?php echo $used; ?> / <?php echo $limit; ?>
                </div>
                <small class="text-muted"><?php echo round($percentage, 1); ?>% used</small>
            </div>
        </div>
        
        <!-- Purchase Credits Section -->
        <div class="dashboard-card">
            <h4 class="mb-4">💳 Purchase More Credits</h4>
            <p class="text-muted">Choose a plan to increase your API call limits</p>
            
            <div class="row">
                <!-- Basic Plan -->
                <div class="col-md-4 mb-3">
                    <div class="plan-card">
                        <h5>🌱 Basic</h5>
                        <div class="plan-price">$5</div>
                        <ul class="list-unstyled">
                            <li>✅ 100 API calls</li>
                            <li>✅ Email support</li>
                            <li>✅ Basic features</li>
                        </ul>
                        <button class="btn btn-outline-primary btn-purchase" onclick="purchaseCredits('basic')">
                            Purchase Basic
                        </button>
                    </div>
                </div>
                
                <!-- Pro Plan -->
                <div class="col-md-4 mb-3">
                    <div class="plan-card recommended">
                        <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-2">RECOMMENDED</span>
                        <h5>⭐ Pro</h5>
                        <div class="plan-price">$15</div>
                        <ul class="list-unstyled">
                            <li>✅ 500 API calls</li>
                            <li>✅ Priority support</li>
                            <li>✅ Advanced features</li>
                            <li>✅ Custom responses</li>
                        </ul>
                        <button class="btn btn-warning btn-purchase" onclick="purchaseCredits('pro')">
                            Purchase Pro
                        </button>
                    </div>
                </div>
                
                <!-- Enterprise Plan -->
                <div class="col-md-4 mb-3">
                    <div class="plan-card">
                        <h5>🏢 Enterprise</h5>
                        <div class="plan-price">$25</div>
                        <ul class="list-unstyled">
                            <li>✅ 1000 API calls</li>
                            <li>✅ 24/7 support</li>
                            <li>✅ Custom integrations</li>
                            <li>✅ White-label options</li>
                        </ul>
                        <button class="btn btn-success btn-purchase" onclick="purchaseCredits('enterprise')">
                            Purchase Enterprise
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function purchaseCredits(plan) {
            // Simulate payment processing
            if (confirm('Purchase ' + plan + ' plan? This is a demo simulation.')) {
                // Show loading state
                event.target.disabled = true;
                event.target.textContent = 'Processing...';
                
                // Simulate API call
                setTimeout(() => {
                    window.location.href = 'add_credits.php?plan=' + plan;
                }, 1000);
            }
        }
        
        // Auto-refresh dashboard every 30 seconds
        setInterval(() => {
            window.location.reload();
        }, 30000);
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
