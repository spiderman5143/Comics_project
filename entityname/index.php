<?php
session_start(); // Start the session

// Handle logout action
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy(); // End the session
    header("Location: index.php"); // Redirect back to the index page
    exit;
}

// Load JSON data
$jsonData = file_get_contents('comics.json');
$comicsData = json_decode($jsonData, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comic Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Comic Collection</h1>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <!-- Show logout button if logged in -->
                <a href="index.php?action=logout" class="btn btn-danger me-2">Logout</a>
            <?php else: ?>
                <!-- Show login button if not logged in -->
                <a href="createAccount.php" class="btn btn-primary me-2">Create Account</a>
                <a href="login.php" class="btn btn-primary me-2">Login</a>
            <?php endif; ?>
        </div>

        <!-- Create button (visible to logged-in users) -->
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <div class="d-flex justify-content-end mb-3">
                <a href="create.php" class="btn btn-success me-2">Add New Series/Issue</a>
                <a href="edit.php" class="btn btn-primary me-2">Edit a Series/Issue</a>
                <a href="delete.php" class="btn btn-danger me-2">Delete a Series/Issue</a>
            </div>
        <?php endif; ?>

        <div class="list-group">
            <?php foreach ($comicsData['comics'] as $series => $issues): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <!-- Link to comic series details -->
                    <a href="detail.php?series=<?php echo urlencode($series); ?>" class="list-group-item list-group-item-action">
                        <?php echo htmlspecialchars($series); ?> (<?php echo count($issues); ?> issues)
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
