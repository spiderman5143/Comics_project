<?php
session_start(); // Start the session
require_once 'db.php'; // Database connection

// Check if the user ID is provided
if (!isset($_GET['user_id'])) {
    header("Location: ../index.php"); // Redirect to the main index if no user ID is provided
    exit;
}

$userId = $_GET['user_id'];

// Query for unique series from the database for the specified user, ordered by series name
$query = $pdo->prepare('SELECT DISTINCT series FROM comics WHERE user_id = ? ORDER BY series');
$query->execute([$userId]);
$seriesData = $query->fetchAll(PDO::FETCH_ASSOC);

// Fetch the username for display purposes
$query = $pdo->prepare('SELECT username FROM users WHERE user_id = ?');
$query->execute([$userId]);
$user = $query->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comics by <?php echo htmlspecialchars($user['username']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Comics by <?php echo htmlspecialchars($user['username']); ?></h1>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-end mb-3">
            <a href="../index.php" class="btn btn-secondary me-2">Back to User List</a>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <!-- Show logout button if logged in -->
                <a href="../index.php?action=logout" class="btn btn-danger me-2">Logout</a>
            <?php else: ?>
                <!-- Show login button if not logged in -->
                <a href="../createAccount.php" class="btn btn-primary me-2">Create Account</a>
                <a href="../login.php" class="btn btn-primary me-2">Login</a>
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
        
        <!-- Display unique series for the user -->
        <div class="list-group">
            <?php foreach ($seriesData as $seriesRow): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="detail.php?series=<?php echo urlencode($seriesRow['series']); ?>&user_id=<?php echo $userId; ?>" class="list-group-item list-group-item-action">
                        <?php echo htmlspecialchars($seriesRow['series']); ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
