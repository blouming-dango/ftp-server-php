<?php
session_start();

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

// Handle search functionality
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchQuery !== '') {
    $users = array_filter($users, fn($user) => stripos($user['username'] ?? '', $searchQuery) !== false);
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $usernameToDelete = $_GET['delete'];
    $users = array_filter($users, fn($user) => ($user['username'] ?? '') !== $usernameToDelete);
    if (file_put_contents($usersFile, json_encode(array_values($users), JSON_PRETTY_PRINT)) === false) {
        $message = "Error: Failed to delete user. Check file permissions.";
    } else {
        $message = "User deleted successfully!";
    }
    header('Location: manage_users.php?message=' . urlencode($message));
    exit;
}

// Display messages from other actions (e.g., deletion)
$message = $_GET['message'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <div class="ocean">
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>
    <div class="container">
        <!-- Logout button -->
        <div class="user-info">
            <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                (<?php echo $_SESSION['role']; ?>)</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <h1>Manage Users</h1>
        <?php if (isset($message)): ?>
            <p style="color: <?php echo strpos($message, 'Error') === false ? 'green' : 'red'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>
        <form method="GET" action="manage_users.php" style="margin-bottom: 20px; display: flex; align-items: center;">
            <input type="text" name="search" placeholder="Search users..."
                value="<?php echo htmlspecialchars($searchQuery); ?>" style="flex: 1; margin-right: 5px;">
            <button type="submit" style="flex-shrink: 0;">Search</button>
        </form>

        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <?php echo htmlspecialchars($user['username'] ?? 'Unknown'); ?>
                    (Role: <?php echo htmlspecialchars($user['role'] ?? 'Unknown'); ?>,
                    Organization: <?php echo htmlspecialchars($user['organization'] ?? 'None'); ?>)
                    <a href="manage_users.php?delete=<?php echo urlencode($user['username'] ?? ''); ?>" style="color: red;"
                        onclick="return confirm('Are you sure?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <button type="button" onclick="window.location.href='upload.php';">Go to Upload Page</button>
        <button type="button" onclick="window.location.href='register.php';">Create New User</button>
    </div>
</body>

</html>