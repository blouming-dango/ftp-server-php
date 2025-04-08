<?php
session_start();

// Define the target directory for uploaded files (outside the web root)
$targetDir = __DIR__ . '/uploads/';
$metadataFile = __DIR__ . '/uploads.json';

// Check if the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

// Check if the user is an admin
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Delete file as admin
if ($isAdmin && isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $filePath = $targetDir . $fileToDelete;

    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            // Remove the file's metadata from uploads.json
            if (file_exists($metadataFile)) {
                $uploads = json_decode(file_get_contents($metadataFile), true);
                $uploads = array_filter($uploads, function ($upload) use ($fileToDelete) {
                    return $upload['filename'] !== $fileToDelete;
                });
                file_put_contents($metadataFile, json_encode(array_values($uploads), JSON_PRETTY_PRINT));
            }
            header('Location: download.php?message=File deleted successfully');
        } else {
            header('Location: download.php?error=Failed to delete the file');
        }
    } else {
        header('Location: download.php?error=File does not exist');
    }
    exit;
}

// Load existing uploads metadata
$uploads = file_exists($metadataFile) ? json_decode(file_get_contents($metadataFile), true) : [];

// As an admin, show all files
if ($_SESSION['role'] === 'admin') {
    $files = array_column($uploads, 'filename'); // Admins can see all files
} else {
    $userFiles = array_filter($uploads, fn($upload) => $upload['uploader'] === $_SESSION['username']);
    $files = array_column($userFiles, 'filename');
}

// Check if the directory exists
if (!is_dir($targetDir)) {
    die("Upload directory does not exist.");
}


// Function to shorten filenames for display
function shortenFilename($filename, $maxLength = 20)
{
    if (strlen($filename) <= $maxLength) {
        return $filename;
    }
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $baseName = substr($filename, 0, strlen($filename) - strlen($extension) - 1);
    $shortenedBase = substr($baseName, 0, $maxLength - strlen($extension) - 3) . '...';
    return $shortenedBase . '.' . $extension;
}

// Handle file download
if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filePath = $targetDir . $file;

    // Check if the file exists and the user is allowed to download it
    $isUserFile = in_array($file, $files);
    if (($isAdmin || $isUserFile) && file_exists($filePath)) {
        $fileType = mime_content_type($filePath);
        // Set headers for file download
        header('Content-Type: ' . $fileType);
        header('Content-Description: File Transfer');

        // Force download for all file types, including images
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');

        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // Output the file content
        readfile($filePath);
        exit;
    } else {
        echo "File not found or you don't have permission to download it.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Download Files</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>

<body>

    <!-- Zeegolf animatie -->
    <div class="ocean">
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>

    <div class="container">
        <!-- Ingelogd als blokje -->
        <div class="user-info">
            <p>Logged in as: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>
                (<?php echo $_SESSION['role']; ?>)</p>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
        <!-- Succesful delete message -->
        <?php if (isset($_GET['message'])): ?>
            <p style="color: green;"><?php echo htmlspecialchars($_GET['message']); ?></p>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <p style="color: red;"><?php echo htmlspecialchars($_GET['error']); ?></p>
        <?php endif; ?>
        <h1>Download Files</h1>
        <h2>Uploaded Files</h2>
        <?php if (empty($files)): ?>
            <p>No files uploaded yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($files as $file): ?>
                    <li>
                        <span><?php echo htmlspecialchars(shortenFilename($file)); ?></span>
                        <?php if (file_exists("{$targetDir}{$file}")): ?>
                            (Size: <?= round(filesize("{$targetDir}{$file}") / 1024, 2); ?> KB,
                            Uploaded: <?= date("Y-m-d H:i:s", filemtime("{$targetDir}{$file}")); ?>)
                        <?php else: ?>
                            (File no longer exists)
                        <?php endif; ?>
                        <form method="GET" action="download.php" style="display: inline;">
                            <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                            <button type="submit"
                                style="background-color: #015871; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Download</button>
                        </form>
                        <?php if ($isAdmin): ?>
                            <a href="download.php?delete=<?php echo urlencode($file); ?>" style="color: red; margin-left: 10px;"
                                onclick="return confirm('Are you sure?');">Delete</a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <button type="button" onclick="location.href='upload.php'">Back</button>
    </div>
</body>

</html>

<?php

?>