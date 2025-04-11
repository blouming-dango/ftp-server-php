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

// Filter users based on role
$displayUsers = $users;
if ($_SESSION['role'] === 'admin' && isset($_SESSION['organization'])) {
    // Admins only see users (not admins or superusers) in their organization
    $displayUsers = array_filter(
        $users,
        fn($user) =>
        isset($user['role']) && $user['role'] === 'user' &&
        isset($user['organization']) && $user['organization'] === $_SESSION['organization']
    );
} // Superusers see all users (no filtering)

// Handle search functionality
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchQuery !== '') {
    $displayUsers = array_filter(
        $displayUsers,
        fn($user) =>
        stripos($user['username'] ?? '', $searchQuery) !== false
    );
}

// Handle user deletion
if (isset($_GET['delete'])) {
    $usernameToDelete = $_GET['delete'];
    // Only allow deletion of users the logged-in user can see
    $canDelete = false;
    foreach ($users as $user) {
        if ($user['username'] === $usernameToDelete) {
            if ($_SESSION['role'] === 'superuser') {
                $canDelete = true; // Superusers can delete anyone
            } elseif (
                $_SESSION['role'] === 'admin' &&
                isset($_SESSION['organization']) &&
                isset($user['role']) && $user['role'] === 'user' &&
                isset($user['organization']) && $user['organization'] === $_SESSION['organization']
            ) {
                $canDelete = true; // Admins can delete users in their org
            }
            break;
        }
    }

    if ($canDelete) {
        $users = array_filter($users, fn($user) => ($user['username'] ?? '') !== $usernameToDelete);
        if (file_put_contents($usersFile, json_encode(array_values($users), JSON_PRETTY_PRINT)) === false) {
            $message = "Error: Failed to delete user. Check file permissions.";
        } else {
            $message = "User deleted successfully!";
        }
    } else {
        $message = "Error: You are not authorized to delete this user.";
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
                (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <h1>Manage Users</h1>
        <?php if ($message): ?>
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
            <?php foreach ($displayUsers as $user): ?>
                <li>
                    <?php echo htmlspecialchars($user['username'] ?? 'Unknown'); ?>
                    (Email: <?php echo htmlspecialchars($user['email'] ?? 'None'); ?>,
                    Role: <?php echo htmlspecialchars($user['role'] ?? 'Unknown'); ?>,
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