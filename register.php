<?php
session_start();
if (!isset($_SESSION['loggedin']) || !in_array($_SESSION['role'], ['superuser', 'admin'])) {
    header('Location: login.php');
    exit;
}

$usersFile = __DIR__ . '/users.json';
$organizationsFile = __DIR__ . '/organizations.json';
$organizations = file_exists($organizationsFile) ? json_decode(file_get_contents($organizationsFile), true) : [];

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Invalid CSRF token.");
    }

    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $organization = $_POST['organization'] ?? null;

    // Validatie gebaseerd op ingelogde gebruiker
    if ($_SESSION['role'] === 'admin' && ($role !== 'user' || $organization !== $_SESSION['organization'])) {
        $error = "Admins can only create users for their own organization.";
    } elseif ($_SESSION['role'] === 'superuser' && $role === 'user' && empty($organization)) {
        $error = "Organization is required for users.";
    }

    // Controleer of de gebruikersnaam al bestaat
    $users = file_exists($usersFile) ? json_decode(file_get_contents($usersFile), true) : [];
    foreach ($users as $user) {
        if ($user['username'] === $username) {
            $error = "Username already exists.";
            break;
        }
    }

    // Voeg nieuwe gebruiker toe
    if (!isset($error)) {
        $users[] = [
            'username' => $username,
            'password' => $password,
            'role' => $role,
            'organization' => $organization
        ];
        file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
        header('Location: manage_users.php?message=User created successfully');
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
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="register.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <br>
            <label for="role">Role:</label>
            <select name="role" id="role">
                <?php if ($_SESSION['role'] === 'superuser'): ?>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                <?php else: ?>
                    <option value="user">User</option>
                <?php endif; ?>
            </select>
            <br>
            <label for="organization">Organization:</label>
            <?php if ($_SESSION['role'] === 'superuser'): ?>
                <select name="organization" id="organization">
                    <option value="">Select an organization</option>
                    <?php foreach ($organizations as $org): ?>
                        <option value="<?php echo htmlspecialchars($org['name']); ?>">
                            <?php echo htmlspecialchars($org['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="text" name="organization" id="organization"
                    value="<?php echo isset($_SESSION['organization']) ? htmlspecialchars($_SESSION['organization']) : ''; ?>">
            <?php endif; ?>
            <br>
            <button type="button" onclick="window.location.href='index.php';">Home</button>
            <button type="submit">Register</button>
        </form>
    </div>
</body>

</html>