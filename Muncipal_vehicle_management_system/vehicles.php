<?php
require_once 'config.php';
startSecureSession();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Handle status update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['vehicle_id']) && isset($_POST['status'])) {
    $vehicle_id = test_input($_POST['vehicle_id']);
    $status = test_input($_POST['status']);

    if (in_array($status, ['Active', 'Inactive', 'Maintenance'])) {
        $conn = getDBConnection();
        $sql = "UPDATE vehicles SET status = ?, last_updated = CURRENT_TIMESTAMP WHERE vehicle_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $vehicle_id);

        if ($stmt->execute()) {
            $successMessage = "Status updated successfully!";
        } else {
            $errorMessage = "Failed to update status. Please try again.";
            error_log("Update error: " . $conn->error, 3, __DIR__ . '/logs/errors.log');
        }
        $stmt->close();
        $conn->close();
    } else {
        $errorMessage = "Invalid status value.";
    }
}

// Fetch vehicles
$conn = getDBConnection();
$sql = "SELECT * FROM vehicles";
$result = $conn->query($sql);
$vehicles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

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
    <title>Vehicles</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f0f0f0; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background-color: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #4CAF50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .status-form { display: inline; }
        select { padding: 5px; border-radius: 4px; }
        button { background-color: #4CAF50; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #45a049; }
        .error { color: red; font-size: 0.9em; }
        .success { color: green; font-size: 0.9em; }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h2>Vehicles</h2>
        <?php if (isset($successMessage)): ?>
            <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>
        <?php if (isset($errorMessage)): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
        <?php if (empty($vehicles)): ?>
            <p>No vehicles found.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Vehicle ID</th>
                    <th>Registration Number</th>
                    <th>Vehicle Type</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle['vehicle_id']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['registration_number']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['vehicle_type']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['status']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle['last_updated']); ?></td>
                        <td>
                            <form class="status-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                <input type="hidden" name="vehicle_id" value="<?php echo htmlspecialchars($vehicle['vehicle_id']); ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="Active" <?php echo $vehicle['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="Inactive" <?php echo $vehicle['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="Maintenance" <?php echo $vehicle['status'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                </select>
                                <!-- Submit button can be optional with onchange -->
                                <button type="submit" style="display: none;">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>