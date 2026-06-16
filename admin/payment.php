<?php
// Payment Processing Page
include __DIR__ . '/auth_admin.php';
include __DIR__ . '/../database/db_connect.php';

$plan = $_GET['plan'] ?? '';
$success = false;
$message = '';
$processedPayment = false;

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
                    $planName = 'Basic';
                    $planPrice = '$5';
                    $message = 'Basic plan purchased! 100 credits added to your account.';
                    break;
                    
                case 'pro':
                    $newLimit = $currentLimit + 500;
                    $planName = 'Pro';
                    $planPrice = '$15';
                    $message = 'Pro plan purchased! 500 credits added to your account.';
                    break;
                    
                case 'enterprise':
                    $newLimit = 1000;
                    $planName = 'Enterprise';
                    $planPrice = '$25';
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
            $processedPayment = true;
            
            // Log the purchase
            error_log("Payment processed: " . $plan . " plan - New limit: " . $newLimit);
            
        } else {
            throw new Exception('Quota record not found');
        }
        
    } catch (Exception $e) {
        error_log("Payment Error: " . $e->getMessage());
        $message = 'Error processing payment: ' . $e->getMessage();
    }
}

include __DIR__ . '/component/nav_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Travel Website Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .payment-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .payment-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            padding: 40px;
            text-align: center;
        }
        
        .plan-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .payment-btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            margin-bottom: 15px;
        }
        
        .payment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .esewa { background: #009639; color: white; }
        .esewa:hover { background: #0056b3; }
        
        .khalti { background: #8B4513; color: white; }
        .khalti:hover { background: #6B3410; }
        
        .stripe { background: #635BFF; color: white; }
        .stripe:hover { background: #4A5BC5; }
        
        .paypal { background: #FFC439; color: white; }
        .paypal:hover { background: #E6B01A; }
        
        .success-animation {
            animation: success-pulse 2s ease-in-out;
        }
        
        @keyframes success-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .processing {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="main-content flex-grow-1 p-4">
        <div class="header mb-4">
            <h1 class="h3">💳 Complete Your Purchase</h1>
            <p class="text-muted">Secure payment processing for your API credits</p>
        </div>
        
        <?php if ($success): ?>
            <!-- Success Message -->
            <div class="payment-card success-animation">
                <div style="font-size: 4rem; color: #28a745; margin-bottom: 20px;">✅</div>
                <h4 class="text-success">Payment Successful!</h4>
                <p class="lead"><?php echo htmlspecialchars($message); ?></p>
                <div class="plan-details">
                    <h5>📋 Plan Details</h5>
                    <p><strong>Plan:</strong> <?php echo htmlspecialchars($planName); ?></p>
                    <p><strong>Price:</strong> <?php echo htmlspecialchars($planPrice); ?></p>
                    <p><strong>New Limit:</strong> <?php echo $newLimit; ?> API calls/month</p>
                </div>
                <a href="api_usage_enhanced.php" class="payment-btn esewa">View Updated Usage</a>
            </div>
            
        <?php elseif ($plan === ''): ?>
            <!-- Plan Selection Error -->
            <div class="payment-card">
                <div style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;">❌</div>
                <h4 class="text-danger">Error</h4>
                <p>No plan selected. Please choose a plan from the usage page.</p>
                <a href="api_usage_enhanced.php" class="payment-btn esewa">Choose Plan</a>
            </div>
            
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$processedPayment): ?>
            <!-- Processing Error -->
            <div class="payment-card">
                <div style="font-size: 4rem; color: #dc3545; margin-bottom: 20px;">❌</div>
                <h4 class="text-danger">Payment Failed</h4>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="api_usage_enhanced.php" class="payment-btn esewa">Try Again</a>
            </div>
            
        <?php else: ?>
            <!-- Payment Form -->
            <div class="payment-container">
                <div class="payment-card">
                    <h4 class="mb-4">🛒 Payment Details</h4>
                    
                    <div class="plan-details">
                        <h5>📋 Selected Plan</h5>
                        <p><strong>Plan:</strong> <?php echo ucfirst($plan); ?> Plan</p>
                        <p><strong>Price:</strong> 
                            <?php 
                            switch ($plan) {
                                case 'basic': echo '$5'; break;
                                case 'pro': echo '$15'; break;
                                case 'enterprise': echo '$25'; break;
                                default: echo 'N/A'; 
                            }
                            ?>
                        </p>
                        <p><strong>API Calls:</strong> 
                            <?php 
                            switch ($plan) {
                                case 'basic': echo '+100'; break;
                                case 'pro': echo '+500'; break;
                                case 'enterprise': echo 'Unlimited'; break;
                                default: echo 'N/A'; 
                            }
                            ?>
                        </p>
                    </div>
                    
                    <h5 class="mb-4">💳 Choose Payment Method</h5>
                    
                    <div class="payment-methods">
                        <!-- eSewa (Working) -->
                        <button class="payment-btn esewa" onclick="processPayment('esewa')">
                            🇳🇵 Pay with eSewa
                        </button>
                        
                        <!-- Khalti (Demo) -->
                        <button class="payment-btn khalti" onclick="processPayment('khalti')">
                            🇰🇳 Pay with Khalti
                        </button>
                        
                        <!-- Stripe (Demo) -->
                        <button class="payment-btn stripe" onclick="processPayment('stripe')">
                            💳 Pay with Stripe
                        </button>
                        
                        <!-- PayPal (Demo) -->
                        <button class="payment-btn paypal" onclick="processPayment('paypal')">
                            💰 Pay with PayPal
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Processing Overlay -->
            <div id="processing" class="processing">
                <div class="spinner"></div>
                <h5>Processing Payment...</h5>
                <p class="text-muted">Please wait while we process your payment.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function processPayment(method) {
            // Show processing overlay
            document.getElementById('processing').style.display = 'block';
            
            // Simulate payment processing
            setTimeout(() => {
                // For demo, only eSewa works
                if (method === 'esewa') {
                    // Submit the form
                    document.querySelector('form').submit();
                } else {
                    // Show demo message for other methods
                    alert('This is a demo. Only eSewa payment is implemented for this demo.');
                    document.getElementById('processing').style.display = 'none';
                }
            }, 2000);
        }
    </script>
    
    <form method="POST" action="">
        <input type="hidden" name="plan" value="<?php echo htmlspecialchars($plan); ?>">
        <input type="hidden" name="confirm_payment" value="1">
    </form>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
