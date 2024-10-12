<?php
session_start(); //starts session

// Load the JSON data
$jsonData = file_get_contents('comics.json');
$comicsData = json_decode($jsonData, true); // Decode to associative array

// Get the series from the URL parameter
$series = isset($_GET['series']) ? $_GET['series'] : '';

// Check if the series exists in the data
if (array_key_exists($series, $comicsData['comics'])) {
    $issues = $comicsData['comics'][$series];
} else {
    $issues = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $series; ?> - Comic Book Issues</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-4">
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>
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

        <h1 class="text-center"><?php echo $series; ?> Issues</h1>
        
        <!-- Grid for displaying comic issues -->
        <div class="row">
            <?php foreach ($issues as $index => $issue) { ?>
                <div class="col-md-3 text-center mb-4">
                    <!-- Display the comic picture -->
                    <img src="comic_images/<?php echo $issue['picture']; ?>" style="width: 150px; height: 200px;">
                    
                    <!-- Display the title -->
                    <h5 class="mt-2"><?php echo $issue['title']; ?></h5>

                    <!-- Display the date issued -->
                    <p>Issue Date: <?php echo $issue['date_issued']; ?></p>
                </div>

                <!-- Create a new row after every 4 comics -->
                <?php if (($index + 1) % 4 == 0) { ?>
                    </div><div class="row">
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
