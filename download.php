<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit;
}

/**
 * Shortens a filename to a specified length, adding ellipsis if necessary.
 *
 * @param string $filename The original filename.
 * @param int $maxLength The maximum length of the shortened filename.
 * @return string The shortened filename.
 */
function shortenFilename($filename, $maxLength = 20)
{
    if (strlen($filename) > $maxLength) {
        return substr($filename, 0, $maxLength - 3) . '...';
    }
    return $filename;
}

$targetDir = __DIR__ . '/uploads/';
$metadataFile = __DIR__ . '/uploads.json';
$isAdmin = $_SESSION['role'] === 'admin';
$isSuperuser = $_SESSION['role'] === 'superuser';

// Verwijder bestand als admin of superuser
if (($isAdmin || $isSuperuser) && isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $filePath = $targetDir . $fileToDelete;

    if (file_exists($filePath)) {
        $uploads = json_decode(file_get_contents($metadataFile), true);
        $fileOrg = null;
        foreach ($uploads as $upload) {
            if ($upload['filename'] === $fileToDelete) {
                $fileOrg = $upload['organization'];
                break;
            }
        }
        if ($isSuperuser || ($isAdmin && $fileOrg === $_SESSION['organization'])) {
            if (unlink($filePath)) {
                $uploads = array_filter($uploads, fn($upload) => $upload['filename'] !== $fileToDelete);
                file_put_contents($metadataFile, json_encode(array_values($uploads), JSON_PRETTY_PRINT));
                header('Location: download.php?message=File deleted successfully');
            } else {
                header('Location: download.php?error=Failed to delete the file');
            }
        } else {
            header('Location: download.php?error=Permission denied');
        }
    } else {
        header('Location: download.php?error=File does not exist');
    }
    exit;
}

// Laad uploads metadata
$uploads = file_exists($metadataFile) ? json_decode(file_get_contents($metadataFile), true) : [];

// Filter bestanden gebaseerd op rol
if ($isSuperuser) {
    $files = array_column($uploads, 'filename');
} elseif ($isAdmin) {
    $userFiles = array_filter($uploads, fn($upload) => $upload['organization'] === $_SESSION['organization']);
    $files = array_column($userFiles, 'filename');
} else {
    $userFiles = array_filter($uploads, fn($upload) => $upload['uploader'] === $_SESSION['username'] && $upload['organization'] === $_SESSION['organization']);
    $files = array_column($userFiles, 'filename');
}

// Download logica
if (isset($_GET['file'])) {
    $file = basename($_GET['file']);
    $filePath = $targetDir . $file;
    $isUserFile = in_array($file, $files);
    if (($isSuperuser || $isAdmin || $isUserFile) && file_exists($filePath)) {
        $fileType = mime_content_type($filePath);
        header('Content-Type: ' . $fileType);
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
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
                        <?php if ($isAdmin || $isSuperuser): ?>
                            <a href="download.php?delete=<?php echo urlencode($file); ?>" style="color: red;"
                                onclick="return confirm('Are you sure?');">Delete</a>
                        <?php endif; ?>
                        <form method="GET" action="download.php" style="display: inline;">
                            <input type="hidden" name="file" value="<?php echo htmlspecialchars($file); ?>">
                            <button type="submit"
                                style="background-color: #015871; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Download</button>
                        </form>
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