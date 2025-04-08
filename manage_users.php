<?php
session_start();
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['superuser', 'admin'])) {
    header('Location: login.php');
    exit;
}

$usersFile = __DIR__ . '/users.json';
$users = json_decode(file_get_contents($usersFile), true);

// Filter gebruikers gebaseerd op rol
if ($_SESSION['role'] === 'admin') {
    $users = array_filter($users, fn($user) => $user['organization'] === $_SESSION['organization']);
}

// Handle zoekfunctionaliteit
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
if ($searchQuery !== '') {
    $users = array_filter($users, fn($user) => stripos($user['username'], $searchQuery) !== false);
}

// Handle gebruikersverwijdering
if (isset($_GET['delete'])) {
    $usernameToDelete = $_GET['delete'];
    $users = array_filter($users, fn($user) => $user['username'] !== $usernameToDelete);
    file_put_contents($usersFile, json_encode(array_values($users), JSON_PRETTY_PRINT));
    header('Location: manage_users.php?message=User deleted successfully');
    exit;
}
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
        <h1>Manage Users</h1>
        <form method="GET" action="manage_users.php" style="margin-bottom: 20px;">
            <input type="text" name="search" placeholder="Search users..."
                value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Search</button>
        </form>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <?php echo htmlspecialchars($user['username']); ?>
                    (Role: <?php echo $user['role']; ?>, Organization:
                    <?php echo htmlspecialchars($user['organization'] ?? 'None'); ?>)
                    <a href="manage_users.php?delete=<?php echo urlencode($user['username']); ?>" style="color: red;"
                        onclick="return confirm('Are you sure?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <button type="button" onclick="window.location.href='upload.php';">Go to Upload Page</button>
        <button type="button" onclick="window.location.href='register.php';">Create New User</button>
    </div>
</body>

</html>