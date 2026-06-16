<?php
// Add Credits Processing
include __DIR__ . '/auth_admin.php';
include __DIR__ . '/../database/db_connect.php';

$plan = $_GET['plan'] ?? '';
$success = false;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $plan !== '') {
    try {
        // Get current quota
        $stmt = $pdo->query("SELECT api_call_limit, period_calls FROM chatbot_system_quota WHERE id = 1");
        $quota = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($quota) {
            $currentLimit = $quota['api_call_limit'];
            
            // Calculate new limit based on plan
            switch ($plan) {
                case 'basic':
                    $newLimit = $currentLimit + 100;
                    $message = 'Basic plan purchased! 100 credits added to your account.';
                    break;
                    
                case 'pro':
                    $newLimit = $currentLimit + 500;
                    $message = 'Pro plan purchased! 500 credits added to your account.';
                    break;
                    
                case 'enterprise':
                    $newLimit =1000;
                    $message = 'Enterprise plan activated! 1000 API access.';
                    break;
                    
                default:
                    throw new Exception('Invalid plan selected');
            }
            
            // Update quota
            $updateSql = "UPDATE chatbot_system_quota SET api_call_limit = ?, updated_at = CURRENT_TIMESTAMP WHERE id = 1";
            $stmt = $pdo->prepare($updateSql);
            $stmt->execute([$newLimit]);
            
            $success = true;
            
            // Log the purchase
            error_log("Credits purchased: " . $plan . " plan - New limit: " . $newLimit);
            
        } else {
            throw new Exception('Quota record not found');
        }
        
    } catch (Exception $e) {
        error_log("Add Credits Error: " . $e->getMessage());
        $message = 'Error processing purchase: ' . $e->getMessage();
    }
}

include __DIR__ . '/component/nav_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Credits - Travel Website Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .result-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            margin: 50px auto;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .btn-continue {
            background: #007bff;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        
        .btn-continue:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="main-content flex-grow-1 p-4">
        <div class="header mb-4">
            <h1 class="h3">💳 Purchase Credits</h1>
            <p class="text-muted">Complete your API credit purchase</p>
        </div>
        
        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="result-card">
                <div class="success-icon">✅</div>
                <h4 class="text-success">Purchase Successful!</h4>
                <p class="lead"><?php echo htmlspecialchars($message); ?></p>
                <a href="api_dashboard.php" class="btn-continue">View Dashboard</a>
            </div>
            
        <?php elseif ($plan !== ''): ?>
            <!-- Plan Selection Error -->
            <div class="result-card">
                <div class="error-icon">❌</div>
                <h4 class="text-danger">Error</h4>
                <p>No plan selected. Please choose a plan from the dashboard.</p>
                <a href="api_dashboard.php" class="btn-continue">Choose Plan</a>
            </div>
            
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <!-- Processing Error -->
            <div class="result-card">
                <div class="error-icon">❌</div>
                <h4 class="text-danger">Purchase Failed</h4>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="api_dashboard.php" class="btn-continue">Try Again</a>
            </div>
            
        <?php else: ?>
            <!-- Loading State -->
            <div class="result-card">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h5 class="mt-3">Processing Purchase...</h5>
                    <p class="text-muted">Please wait while we process your payment.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto-redirect after 3 seconds if successful
        <?php if ($success): ?>
        setTimeout(() => {
            window.location.href = 'api_dashboard.php';
        }, 3000);
        <?php endif; ?>
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
