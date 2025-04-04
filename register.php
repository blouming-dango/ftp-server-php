<?php
// filepath: c:\Users\brent\OneDrive\Documenten\Projecten\web-ftp-server\register.php
echo "<link rel='stylesheet' type='text/css' href='public/styles.css' />";

session_start();

// Generate a CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Define the path to the users file
$usersFile = __DIR__ . '/users.json';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $role = $_POST['role'];

    // Load existing users
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];

    // Check if the username already exists
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $error = "Username already exists.";
            break;
        }
    }

    // If no error, add the new user
    if (!isset($error)) {
        $users[] = ['username' => $username, 'password' => $password, 'role' => $role];
        file_put_contents($usersFile, json_encode($users));
        header('Location: login.php?registered=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Registration</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>

    <div class="ocean">
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>

    <div class="container">
        <h1>User Registration</h1>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="register.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <!-- Bestaande velden -->
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <br>
            <label for="role">Role:</label>
            <select name="role" id="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
            <br>
            <button onclick="window.location.href='index.html';" class="button">Home</button>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>

</html>