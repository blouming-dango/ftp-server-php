<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$usersFile = __DIR__ . '/users.json';
$users = json_decode(file_get_contents($usersFile), true);

if (isset($_GET['delete'])) {
    $usernameToDelete = $_GET['delete'];
    $users = array_filter($users, fn($user) => $user['username'] !== $usernameToDelete);
    file_put_contents($usersFile, json_encode(array_values($users)));
    header('Location: manage_users.php');
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
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <?php echo htmlspecialchars($user['username']); ?> (Role: <?php echo $user['role']; ?>)
                    <a href="manage_users.php?delete=<?php echo urlencode($user['username']); ?>" style="color: red;"
                        onclick="return confirm('Are you sure?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <button type="button" onclick="window.location.href='upload.php';">Go to Upload Page</button>
    </div>
</body>

</html>