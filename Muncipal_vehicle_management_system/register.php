<?php
require_once 'config.php';
startSecureSession();

// Initialize variables
$usernameErr = $emailErr = $passwordErr = $confirmPasswordErr = "";
$username = $email = $successMessage = $errorMessage = "";

// Create database connection
$conn = getDBConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errorMessage = "Invalid CSRF token.";
    } else {
        // Validate username
        if (empty($_POST["username"])) {
            $usernameErr = "Username is required";
        } else {
            $username = test_input($_POST["username"]);
            $sql = "SELECT id FROM users WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $usernameErr = "Username already taken";
            }
            $stmt->close();
        }
        
        // Validate email
        if (empty($_POST["email"])) {
            $emailErr = "Email is required";
        } else {
            $email = test final codes
test_input($_POST["email"]);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Invalid email format";
            } else {
                $sql = "SELECT id FROM users WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                if ($stmt->get_result()->num_rows > 0) {
                    $emailErr = "Email already registered";
                }
                $stmt->close();
            }
        }
        
        // Validate password
        if (empty($_POST["password"])) {
            $passwordErr = "Password is required";
        }
        
        // Validate confirm password
        if (empty($_POST["confirm-password"])) {
            $confirmPasswordErr = "Please confirm password";
        } elseif ($_POST["password"] !== $_POST["confirm-password"]) {
            $confirmPasswordErr = "Passwords do not match";
        }
        
        // If no errors, process registration
        if (empty($usernameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmPasswordErr) && empty($errorMessage)) {
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $email, $password);
            
            if ($stmt->execute()) {
                $successMessage = "Registration successful! Redirecting to login...";
                header("refresh:2;url=login.php");
            } else {
                $errorMessage = "Registration failed. Please try again.";
                error_log("Registration error: " . $conn->error, 3, __DIR__ . '/logs/errors.log');
            }
            $stmt->close();
        }
    }
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 400px; margin: 0 auto; padding: 20px; background-color: #f0f0f0; }
        .container { background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ddd; border-radius: 4px; }
        button { background-color: #4CAF50; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #45a049; }
        .login-link { text-align: center; margin-top: 15px; }
        .login-link a { color: #4CAF50; text-decoration: none; }
        .error { color: red; font-size: 0.9em; }
        .success { color: green; font-size: 1em; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (!empty($successMessage)): ?>
            <p class="success"><?php echo htmlspecialchars($successMessage); ?></p>
        <?php endif; ?>
        <?php if (!empty($errorMessage)): ?>
            <p class="error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                <span class="error"><?php echo htmlspecialchars($usernameErr); ?></span>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <span class="error"><?php echo htmlspecialchars($emailErr); ?></span>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <span class="error"><?php echo htmlspecialchars($passwordErr); ?></span>
            </div>
            <div class="form-group">
                <label for="confirm-password">Confirm Password:</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
                <span class="error"><?php echo htmlspecialchars($confirmPasswordErr); ?></span>
            </div>
            <button type="submit">Register</button>
            <div class="login-link">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </form>
    </div>
</body>
</html>