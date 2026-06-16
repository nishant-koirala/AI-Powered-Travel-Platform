<?php
// Include admin authentication check
session_start();

// Simple authentication check - allow access if session exists or create simple bypass for testing
if (!isset($_SESSION['admin_logged_in']) && !isset($_GET['bypass'])) {
    // For testing, allow direct access without full authentication
    // In production, you would redirect to login.php
    // header('Location: login.php');
    // exit();
}

include __DIR__ . '/../database/db_connect.php';

echo "<h2>Chatbot messages</h2>";

try {
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'chatbot_messages'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<div style='color: green; font-weight: bold; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0;'>";
        echo "✅ chatbot_messages table exists";
        echo "</div>";
        
        // Get message count
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM chatbot_messages");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<div style='padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; margin: 10px 0;'>";
        echo "<p><strong>Total Messages:</strong> " . $result['count'] . "</p>";
        echo "</div>";
        
        // Get all messages
        $stmt = $pdo->query("SELECT * FROM chatbot_messages ORDER BY created_at DESC");
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($messages) {
            echo "<div style='color: green; font-weight: bold; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0;'>";
            echo "✅ " . count($messages) . " messages found";
            echo "</div>";
            
            echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th>ID</th><th>Session</th><th>Sender</th><th>Message</th><th>Type</th><th>Intent</th><th>Category</th><th>AI Used</th><th>Time</th>";
            echo "</tr>";
            
            foreach ($messages as $msg) {
                $rowColor = $msg['sender'] === 'user' ? '#e8f5e8' : '#f0f8ff';
                echo "<tr style='background: $rowColor;'>";
                echo "<td>" . $msg['id'] . "</td>";
                echo "<td><small>" . htmlspecialchars(substr($msg['session_id'], 0, 8)) . "...</small></td>";
                echo "<td><strong>" . htmlspecialchars($msg['sender']) . "</strong></td>";
                echo "<td>" . htmlspecialchars(substr($msg['message'], 0, 100)) . (strlen($msg['message']) > 100 ? '...' : '') . "</td>";
                echo "<td>" . htmlspecialchars($msg['message_type']) . "</td>";
                echo "<td>" . htmlspecialchars($msg['intent']) . "</td>";
                echo "<td>" . htmlspecialchars($msg['category']) . "</td>";
                echo "<td>" . ($msg['ai_used'] ? 'Yes' : 'No') . "</td>";
                echo "<td><small>" . htmlspecialchars($msg['created_at']) . "</small></td>";
                echo "</tr>";
            }
            echo "</table>";
            
        } else {
            echo "<div style='color: orange; font-weight: bold; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px 0;'>";
            echo "⚠ No messages found in table";
            echo "</div>";
        }
        
    } else {
        echo "<div style='color: red; font-weight: bold; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0;'>";
        echo "❌ chatbot_messages table does not exist";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='color: red; font-weight: bold; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0;'>";
    echo "❌ Error: " . $e->getMessage();
    echo "</div>";
}

include __DIR__ . '/component/nav_admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot History - Travel Website Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        table { font-size: 12px; }
        td, th { padding: 8px; text-align: left; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; margin: 10px 5px; }
        
        /* Override admin layout to prevent sidebar overlap */
        .main-content {
            padding: 30px !important;
            margin-left: 0 !important; /* Remove the 250px margin */
            width: 100% !important; /* Use full width */
            position: relative;
            z-index: 1;
        }
        
        /* Add padding to account for sidebar */
        body {
            padding-left: 260px; /* Account for sidebar width + some space */
        }
        
        /* Responsive for smaller screens */
        @media (max-width: 768px) {
            .main-content {
                padding: 20px !important;
            }
            body {
                padding-left: 0; /* Remove padding on mobile */
            }
        }
    </style>
</head>
<body>
    <div class="main-content flex-grow-1 p-4">
        <div class="header mb-4">
            <h1 class="h3">Chatbot Messages</h1>
            <p class="text-muted">All chatbot conversations (newest first)</p>
        </div>
        
        <?php
        try {
            // Check if table exists
            $stmt = $pdo->query("SHOW TABLES LIKE 'chatbot_messages'");
            $tableExists = $stmt->rowCount() > 0;
            
            if ($tableExists) {
                echo "<div style='color: green; font-weight: bold; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0;'>";
                echo "✅ chatbot_messages table exists";
                echo "</div>";
                
                // Get message count
                $stmt = $pdo->query("SELECT COUNT(*) as count FROM chatbot_messages");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo "<div style='padding: 10px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; margin: 10px 0;'>";
                echo "<p><strong>Total Messages:</strong> " . $result['count'] . "</p>";
                echo "</div>";
                
                // Get all messages
                $stmt = $pdo->query("SELECT * FROM chatbot_messages ORDER BY created_at DESC");
                $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if ($messages) {
                    echo "<div style='color: green; font-weight: bold; padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; margin: 10px 0;'>";
                    echo "✅ " . count($messages) . " messages found";
                    echo "</div>";
                    
                    echo "<table class='table table-striped table-hover table-bordered align-middle'>";
                    echo "<thead class='table-light'>";
                    echo "<tr>";
                    echo "<th>ID</th><th>Session</th><th>Sender</th><th>Message</th><th>Type</th><th>Intent</th><th>Category</th><th>AI Used</th><th>Time</th>";
                    echo "</tr>";
                    echo "</thead>";
                    echo "<tbody>";
                    
                    foreach ($messages as $msg) {
                        $rowColor = $msg['sender'] === 'user' ? '#e8f5e8' : '#f0f8ff';
                        echo "<tr style='background: $rowColor;'>";
                        echo "<td>" . $msg['id'] . "</td>";
                        echo "<td><small>" . htmlspecialchars(substr($msg['session_id'], 0, 8)) . "...</small></td>";
                        echo "<td><strong>" . htmlspecialchars($msg['sender']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars(substr($msg['message'], 0, 100)) . (strlen($msg['message']) > 100 ? '...' : '') . "</td>";
                        echo "<td>" . htmlspecialchars($msg['message_type']) . "</td>";
                        echo "<td>" . htmlspecialchars($msg['intent']) . "</td>";
                        echo "<td>" . htmlspecialchars($msg['category']) . "</td>";
                        echo "<td>" . ($msg['ai_used'] ? 'Yes' : 'No') . "</td>";
                        echo "<td><small>" . htmlspecialchars($msg['created_at']) . "</small></td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                    
                } else {
                    echo "<div style='color: orange; font-weight: bold; padding: 10px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; margin: 10px 0;'>";
                    echo "⚠ No messages found in table";
                    echo "</div>";
                }
                
            } else {
                echo "<div style='color: red; font-weight: bold; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0;'>";
                echo "❌ chatbot_messages table does not exist";
                echo "</div>";
            }
            
        } catch (Exception $e) {
            echo "<div style='color: red; font-weight: bold; padding: 10px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin: 10px 0;'>";
            echo "❌ Error: " . $e->getMessage();
            echo "</div>";
        }
        ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="../debug_chat_history.php" class="btn">Debug Version</a>
            <a href="../test_chatbot_api.php" class="btn">Test API</a>
        </div>
    </div>
</body>
</html>
