<?php
session_start();

// Restrict access to superuser or admin
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['superuser', 'admin'])) {
    header('Location: login.php');
    exit;
}

$usersFile = __DIR__ . '/users.json';
// Load users, default to empty array if file is invalid
$users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
if (!is_array($users)) {
    $users = [];
}

// Handle registration
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newUser = [
        'username' => filter_var($_POST['username'] ?? '', FILTER_SANITIZE_STRING),
        'password' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
        'email' => filter_var($_POST['email'] ?? ''),
    ];

    // Debug session values
    $debugLog = "Register attempt: role={$_SESSION['role']}, organization=" . (isset($_SESSION['organization']) ? $_SESSION['organization'] : 'unset') . "\n";
    file_put_contents('debug.log', $debugLog, FILE_APPEND);

    // Role and organization based on logged-in user
    if ($_SESSION['role'] === 'admin' && isset($_SESSION['organization'])) {
        $newUser['role'] = 'user';
        $newUser['organization'] = $_SESSION['organization'];
    } elseif ($_SESSION['role'] === 'superuser') {
        $newUser['role'] = 'admin';
        $newUser['organization'] = filter_var($_POST['organization'] ?? '', FILTER_SANITIZE_STRING);
    } else {
        $message = "Error: Unauthorized action (role: {$_SESSION['role']}, org: " . (isset($_SESSION['organization']) ? $_SESSION['organization'] : 'unset') . ").";
    }

    // Validate inputs
    if (!$message) {
        // Check required fields
        if (empty($newUser['username'])) {
            $message = "Error: Username is required.";
        } elseif (empty($_POST['password'])) {
            $message = "Error: Password is required.";
        } elseif (empty($newUser['email']) || !filter_var($newUser['email'], FILTER_VALIDATE_EMAIL)) {
            $message = "Error: A valid email address is required.";
        } elseif ($_SESSION['role'] === 'superuser' && empty($newUser['organization'])) {
            $message = "Error: Organization is required for admin accounts.";
        } else {
            // Check for duplicate username or email
            $usernameExists = array_filter($users, fn($user) => isset($user['username']) && $user['username'] === $newUser['username']);
            $emailExists = array_filter($users, fn($user) => isset($user['email']) && $user['email'] === $newUser['email']);
            if (!empty($usernameExists)) {
                $message = "Error: Username already exists.";
            } elseif (!empty($emailExists)) {
                $message = "Error: Email address is already in use.";
            } else {
                $users[] = $newUser;
                if (file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT)) === false) {
                    $message = "Error: Failed to save user. Check file permissions.";
                } else {
                    $message = "User registered successfully!";
                    header('Location: manage_users.php?message=' . urlencode($message));
                    exit;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Register User</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <div class="ocean">
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>
    <div class="container">
        <h1>Register New User</h1>
        <?php if ($message): ?>
            <p style="color: <?php echo strpos($message, 'Error') === false ? 'green' : 'red'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter email" required>
            <?php if ($_SESSION['role'] === 'superuser'): ?>
                <label for="organization">Organization:</label>
                <input type="text" id="organization" name="organization" placeholder="Enter organization" required>
            <?php endif; ?>
            <button type="submit">Register</button>
        </form>
        <div class="button-container">
            <button type="button" onclick="window.location.href='manage_users.php';">Back to Manage Users</button>
        </div>
    </div>
</body>

</html>