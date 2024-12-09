<?php
session_start(); // Start the session
require_once 'db.php'; // Database connection

// Get the series and user_id from the URL
if (isset($_GET['series']) && isset($_GET['user_id'])) {
    $seriesName = $_GET['series'];
    $userId = $_GET['user_id'];

    // Query to get all issues for the specific series by the specific user, ordered by issue date ascending
    $query = $pdo->prepare('
        SELECT c.comic_id, c.title, c.issue_date, c.picture, u.username
        FROM comics c
        JOIN users u ON c.user_id = u.user_id
        WHERE c.series = :series AND c.user_id = :user_id
        ORDER BY c.issue_date ASC
    ');
    $query->execute(['series' => $seriesName, 'user_id' => $userId]);

    // Fetch all issues for the series and user
    $issuesData = $query->fetchAll(PDO::FETCH_ASSOC);
} else {
    echo "No series or user specified.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Series Details - <?php echo htmlspecialchars($seriesName); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center"><?php echo htmlspecialchars($seriesName); ?> Issues</h1>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-end mb-3">
        <a href="index.php?user_id=<?php echo urlencode($userId); ?>" class="btn btn-secondary me-2">Back to Series List</a>            
        <a href="index.php" class="btn btn-secondary me-2">Back to User List</a>
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <!-- Show logout button if logged in -->
                <a href="../index.php?action=logout" class="btn btn-danger me-2">Logout</a>
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

        <!-- Display series issues -->
        <?php if (count($issuesData) > 0): ?>
            <div class="row">
                <?php foreach ($issuesData as $issue): ?>
                    <div class="col-md-3 mb-3">
                        <div class="card">
                            <img src="comic_images/<?php echo htmlspecialchars($issue['picture']); ?>" class="card-img-top" alt="Issue Cover">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($issue['title']); ?></h5>
                                <p class="card-text">Issued on: <?php echo htmlspecialchars($issue['issue_date']); ?></p>
                                <p class="card-text"><small>Created by: <?php echo htmlspecialchars($issue['username']); ?></small></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No issues found for this series by this user.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
