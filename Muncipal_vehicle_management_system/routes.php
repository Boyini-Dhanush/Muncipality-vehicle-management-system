<?php
require_once 'config.php';
startSecureSession();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Handle status and garbage updates
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = getDBConnection();

    if (isset($_POST['route_id']) && isset($_POST['action'])) {
        $route_id = test_input($_POST['route_id']);
        $action = test_input($_POST['action']);

        if ($action === 'start') {
            $sql = "UPDATE routes SET status = 'Started', start_time = CURRENT_TIME WHERE route_id = ?";
            $message = "Route started successfully!";
        } elseif ($action === 'stop') {
            $sql = "UPDATE routes SET status = 'Not Started', end_time = CURRENT_TIME WHERE route_id = ?";
            $message = "Route stopped successfully!";
        } elseif ($action === 'collect') {
            $sql = "UPDATE routes SET garbage = 'Collected' WHERE route_id = ? AND garbage = 'Not Collected'";
            $message = "Garbage marked as collected!";
        } elseif ($action === 'not_collected') {
            $sql = "UPDATE routes SET garbage = 'Not Collected' WHERE route_id = ? AND garbage = 'Collected'";
            $message = "Garbage marked as not collected!";
        }

        if (isset($sql)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $route_id);

            if ($stmt->execute()) {
                $successMessage = $message;
                // Debugging: Log the update
                error_log("Update successful for route_id $route_id, action: $action, time: " . date('Y-m-d H:i:s'), 3, __DIR__ . '/logs/debug.log');
            } else {
                $errorMessage = "Failed to update route. Please try again. Error: " . $conn->error;
                error_log("Update failed for route_id $route_id, action: $action, error: " . $conn->error, 3, __DIR__ . '/logs/errors.log');
            }
            $stmt->close();
        }
    }

    $conn->close();
}

// Fetch routes
$conn = getDBConnection();
$sql = "SELECT * FROM routes";
$result = $conn->query($sql);
$routes = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

$conn->close();

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routes</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f0f0f0; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .action-buttons { display: flex; gap: 5px; }
        button { background-color: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        button.stop { background-color: #f44336; }
        button.not-collected { background-color: #ff9800; } /* Orange for Not Collected */
        button:hover { opacity: 0.9; }
        button.stop:hover { background-color: #da190b; }
        button.not-collected:hover { background-color: #f57c00; }
        .error { color: red; font-size: 0.9em; }
        .success { color: green; font-size: 0.9em; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2>Routes</h2>
        <?php if (isset($successMessage)): ?>
            <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
        <?php if (empty($routes)): ?>
            <p>No routes found.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Route ID</th>
                    <th>Vehicle ID</th>
                    <th>Route Name</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                    <th>Garbage</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($routes as $route): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($route['route_id']); ?></td>
                        <td><?php echo htmlspecialchars($route['vehicle_id']); ?></td>
                        <td><?php echo htmlspecialchars($route['route_name']); ?></td>
                        <td><?php echo htmlspecialchars($route['start_time'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($route['end_time'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($route['status']); ?></td>
                        <td><?php echo htmlspecialchars($route['garbage']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: inline;">
                                    <input type="hidden" name="route_id" value="<?php echo htmlspecialchars($route['route_id']); ?>">
                                    <input type="hidden" name="action" value="start">
                                    <button type="submit" <?php echo $route['status'] === 'Started' ? 'disabled' : ''; ?>>Start</button>
                                </form>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: inline;">
                                    <input type="hidden" name="route_id" value="<?php echo htmlspecialchars($route['route_id']); ?>">
                                    <input type="hidden" name="action" value="stop">
                                    <button type="submit" class="stop" <?php echo $route['status'] === 'Not Started' ? 'disabled' : ''; ?>>Stop</button>
                                </form>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: inline;">
                                    <input type="hidden" name="route_id" value="<?php echo htmlspecialchars($route['route_id']); ?>">
                                    <input type="hidden" name="action" value="collect">
                                    <button type="submit" <?php echo $route['garbage'] === 'Collected' ? 'disabled' : ''; ?>>Collect Garbage</button>
                                </form>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" style="display: inline;">
                                    <input type="hidden" name="route_id" value="<?php echo htmlspecialchars($route['route_id']); ?>">
                                    <input type="hidden" name="action" value="not_collected">
                                    <button type="submit" class="not-collected" <?php echo $route['garbage'] === 'Not Collected' ? 'disabled' : ''; ?>>Not Collected</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>