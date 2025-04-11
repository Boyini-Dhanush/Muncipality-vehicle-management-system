<?php
require_once 'config.php';
startSecureSession();

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Determine current page for active class
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation Bar</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
        }
        .navbar {
            background-color: #333;
            padding: 1rem;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .nav-list {
            list-style: none;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
        }
        .nav-item {
            position: relative;
        }
        .nav-link {
            color: white;
            text-decoration: none;
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            transition: color 0.3s ease, background-color 0.3s ease;
            border-radius: 5px;
        }
        .nav-link:hover {
            color: #fff;
            background-color: #4CAF50;
        }
        .nav-link.active {
            background-color: #4CAF50;
            color: white;
        }
        .menu-toggle {
            display: none;
            cursor: pointer;
            color: white;
            font-size: 1.5rem;
            position: absolute;
            right: 1rem;
            top: 1rem;
        }
        @media screen and (max-width: 768px) {
            .menu-toggle {
                display: block;
            }
            .nav-list {
                display: none;
                flex-direction: column;
                width: 100%;
                text-align: center;
                padding: 1rem 0;
            }
            .nav-list.active {
                display: flex;
            }
            .nav-item {
                width: 100%;
                padding: 0.5rem 0;
            }
            .nav-link {
                display: block;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="menu-toggle" id="mobile-menu" aria-label="Toggle navigation">
            ☰
        </div>
        <ul class="nav-list">
            <?php if ($isLoggedIn): ?>
                <li class="nav-item">
                    <a href="vehicles.php" class="nav-link <?php echo $currentPage === 'vehicles.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'vehicles.php' ? 'page' : ''; ?>">Vehicles</a>
                </li>
                <li class="nav-item">
                    <a href="drivers.php" class="nav-link <?php echo $currentPage === 'drivers.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'drivers.php' ? 'page' : ''; ?>">Drivers</a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link <?php echo $currentPage === 'users.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'users.php' ? 'page' : ''; ?>">Users</a>
                </li>
                <li class="nav-item">
                    <a href="routes.php" class="nav-link <?php echo $currentPage === 'routes.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'routes.php' ? 'page' : ''; ?>">Routes</a>
                </li>
                <li class="nav-item">
                    <a href="notifications.php" class="nav-link <?php echo $currentPage === 'notifications.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'notifications.php' ? 'page' : ''; ?>">Notifications</a>
                </li>
                <li class="nav-item">
                    <a href="?logout=1" class="nav-link">Logout</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a href="login.php" class="nav-link <?php echo $currentPage === 'login.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'login.php' ? 'page' : ''; ?>">Login</a>
                </li>
                <li class="nav-item">
                    <a href="register.php" class="nav-link <?php echo $currentPage === 'register.php' ? 'active' : ''; ?>" aria-current="<?php echo $currentPage === 'register.php' ? 'page' : ''; ?>">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>

    <script>
        const mobileMenu = document.getElementById('mobile-menu');
        const navList = document.querySelector('.nav-list');

        mobileMenu.addEventListener('click', () => {
            navList.classList.toggle('active');
            mobileMenu.setAttribute('aria-expanded', navList.classList.contains('active'));
        });

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.navbar')) {
                navList.classList.remove('active');
                mobileMenu.setAttribute('aria-expanded', 'false');
            }
        });
    </script>
</body>
</html>