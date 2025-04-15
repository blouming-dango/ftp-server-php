<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'superuser') {
    header('Location: login.php');
    exit;
}

$organizationsFile = __DIR__ . '/organizations.json';
$organizations = file_exists($organizationsFile) ? json_decode(file_get_contents($organizationsFile), true) : [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $organizations[] = [
            'name' => $name,
            'created_by' => $_SESSION['username'],
            'created_at' => date('Y-m-d H:i:s')
        ];
        file_put_contents($organizationsFile, json_encode($organizations, JSON_PRETTY_PRINT));
        header('Location: manage_organizations.php?message=Organization created successfully');
        exit;
    } else {
        $error = "Organization name is required.";
    }
}

// Verwijder organisatie
if (isset($_GET['delete'])) {
    $nameToDelete = $_GET['delete'];
    $organizations = array_filter($organizations, fn($org) => $org['name'] !== $nameToDelete);
    file_put_contents($organizationsFile, json_encode(array_values($organizations), JSON_PRETTY_PRINT));
    header('Location: manage_organizations.php?message=Organization deleted successfully');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Organizations</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>
    <div class="ocean">
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>
    <div class="container">
        <h1>Manage Organizations</h1>
        <?php if (isset($_GET['message'])): ?>
            <p style="color: green;"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="manage_organizations.php">
            <label for="name">Organization Name:</label>
            <input type="text" name="name" id="name" required>
            <button type="submit">Create Organization</button>
        </form>
        <h2>Existing Organizations</h2>
        <ul>
            <?php foreach ($organizations as $org): ?>
                <li>
                    <?php echo htmlspecialchars($org['name']); ?>
                    (Created by: <?php echo htmlspecialchars($org['created_by']); ?> on <?php echo $org['created_at']; ?>)
                    <a href="manage_organizations.php?delete=<?php echo urlencode($org['name']); ?>" style="color: red;"
                        onclick="return confirm('Are you sure?');">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
        <button type="button" onclick="window.location.href='upload.php';">Back</button>
    </div>
</body>

</html>