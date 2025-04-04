<?php
// filepath: c:\Users\brent\OneDrive\Documenten\Projecten\web-ftp-server\upload.php

// Start the session
session_start();

// Controleer of de gebruiker is ingelogd
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Define the target directory for uploaded files (outside the web root)
$targetDir = __DIR__ . '/uploads/';
$metadataFile = __DIR__ . '/uploads.json';

// Check if the directory exists, if not, create it
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Load existing uploads metadata
$uploads = file_exists($metadataFile) ? json_decode(file_get_contents($metadataFile), true) : [];

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if file was uploaded without errors
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $fileName = basename($_FILES['file']['name']);
        $fileSize = $_FILES['file']['size'];
        $fileTmpName = $_FILES['file']['tmp_name'];
        $fileType = mime_content_type($fileTmpName);

        // Define allowed file types and maximum file size (e.g., 2MB)
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf', 'application/zip', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'zip', 'doc', 'docx'];
        $maxFileSize = 100 * 1024 * 1024; // 100MB

        // Fallback to check file extension if MIME type is not reliable
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
            $message = "File type not allowed.";
            exit;
        }

        // Sanitize file name
        $fileName = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $fileName);

        // Generate a unique file name to prevent overwriting
        $uniqueFileName = uniqid() . '-' . $fileName;

        // Define the target file path
        $targetFilePath = $targetDir . $uniqueFileName;

        // Get additional metadata from the form (if any)
        $uploaderName = $_POST['uploader_name'] ?? '';
        $organization = $_POST['organization'] ?? '';
        $email = $_POST['email'] ?? '';

        // Check if the file type is allowed and size is within limit
        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxFileSize) {
            if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                // Voeg metadata toe
                $uploads[] = [
                    'filename' => $uniqueFileName,
                    'uploader' => $_SESSION['username'],
                    'uploader_name' => $uploaderName,
                    'organization' => $organization,
                    'email' => $email,
                    'uploaded_at' => date('Y-m-d H:i:s')
                ];
                file_put_contents($metadataFile, json_encode($uploads, JSON_PRETTY_PRINT));
                $message = "The file $fileName has been uploaded successfully.";
            } else {
                $message = "There was an error moving the uploaded file.";
            }
        } else {
            $message = "File type not allowed or file size exceeds the limit.";
        }
    } else {
        $message = "No file uploaded or there was an error uploading the file.";
    }
}
?>

<!-- HTML form for file upload -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Upload File</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>

    <div class="ocean">
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>

    <div class="container">
        <!-- Signed in block -->
        <div class="user-info">
            <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                (<?php echo $_SESSION['role']; ?>)</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <h1>Upload File</h1>
        <?php if (isset($message)): ?>
            <p><?php echo $message; ?></p>
        <?php endif; ?>
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <label for="file">Choose file to upload:</label>
            <input type="file" name="file" id="file" required>

            <label for="uploader_name">Your Name:</label>
            <input type="text" name="uploader_name" id="uploader_name" required>

            <label for="organization">Organization Name:</label>
            <input type="text" name="organization" id="organization" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <input type="submit" value="Upload File">
        </form>
        <br>
        <button onclick="location.href='download.php'">View and Download Uploaded Files</button>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <button onclick="location.href='manage_users.php'">Manage Users</button>
        <?php endif; ?>
    </div>
</body>

</html>