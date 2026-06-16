<?php
// Include the database connection
include '../database/db_connect.php';

try {
    // Query to fetch messages that are unseen (seen = 0)
    $newMessagesQuery = "SELECT id FROM messages WHERE seen = 0";
    $newMessagesResult = $pdo->query($newMessagesQuery);

    // Count the number of new messages
    $newMessagesCount = $newMessagesResult->rowCount();

    // Query to fetch all messages (including phone)
    $messagesQuery = "SELECT id, name, email, phone, message, submitted_at, seen FROM messages ORDER BY submitted_at DESC";
    $messagesResult = $pdo->query($messagesQuery);
    $messages = $messagesResult->fetchAll(PDO::FETCH_ASSOC);
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
        <h1 class="h3">Received Messages</h1>
    </div>

    <!-- New Messages Side Icon -->
    <?php if ($newMessagesCount > 0): ?>
        <div id="newMessageNotification" class="position-fixed top-0 end-0 p-3">
            <div class="alert alert-info" role="alert">
                <i class="bi bi-envelope-fill"></i> You have <?php echo $newMessagesCount; ?> new message(s).
                <button id="closeNotification" class="btn-close float-end"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Messages Section -->
    <div class="section mt-5">
        <h2 class="h4">Message List</h2>

        <table class="table table-striped mt-3">
            <thead class="table-light">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Phone</th>
                    <th scope="col">Message</th>
                    <th scope="col">Submitted At</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($messages as $msg): ?>
                    <tr <?php echo $msg['seen'] == 0 ? 'class="table-warning"' : ''; ?>>
                        <td><?php echo htmlspecialchars($msg['id']); ?></td>
                        <td><?php echo htmlspecialchars($msg['name']); ?></td>
                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                        <td><?php echo htmlspecialchars($msg['phone']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                        <td><?php echo date('F j, Y, g:i a', strtotime($msg['submitted_at'])); ?></td>
                        <td>
                            <form action="delete_message.php" method="post" class="d-inline float-end ms-2">
                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
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

<!-- Custom JS to Handle Notification -->
<script>
    document.getElementById('closeNotification').addEventListener('click', function() {
        // Hide the notification
        document.getElementById('newMessageNotification').style.display = 'none';
        
        // Send an AJAX request to mark all messages as seen
        fetch('mark_messages_as_seen.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Messages marked as seen');
                }
            });
    });
</script>

<?php
// When the "Messages" page is accessed, mark all unread messages as seen
try {
    $updateQuery = "UPDATE messages SET seen = 1 WHERE seen = 0";
    $pdo->exec($updateQuery);
} catch (PDOException $e) {
    error_log("Error updating messages: " . $e->getMessage());
    die("Error updating messages. Please try again later.");
}
?>

</body>
</html>
