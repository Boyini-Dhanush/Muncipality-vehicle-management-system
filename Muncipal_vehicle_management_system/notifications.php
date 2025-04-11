<?php
require_once 'config.php';
startSecureSession();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$conn = getDBConnection();
$sql = "SELECT * FROM notifications";
$result = $conn->query($sql);
$notifications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f0f0f0; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2>Notifications</h2>
        <?php if (empty($notifications)): ?>
            <p>No notifications found.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Notification ID</th>
                    <th>User ID</th>
                    <th>Vehicle ID</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Sent Time</th>
                </tr>
                <?php foreach ($notifications as $notification): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($notification['notification_id']); ?></td>
                        <td><?php echo htmlspecialchars($notification['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($notification['vehicle_id']); ?></td>
                        <td><?php echo htmlspecialchars($notification['message']); ?></td>
                        <td><?php echo htmlspecialchars($notification['status']); ?></td>
                        <td><?php echo htmlspecialchars($notification['sent_time']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>