<?php
session_start(); // Start the session

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Store the requested page URL in the session
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Current page URL
    header("Location: login.php"); // Redirect to login
    exit;
}

// Check if the user is logged in; if not, redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page
    exit;
}

// Load existing JSON data
$jsonData = file_get_contents('comics.json');
$comicsData = json_decode($jsonData, true);

// Initialize message variable
$message = '';

// Handle form submission
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $issue = $_POST['issue']; // This will be in "YYYY-MM-DD" format
    $series = $_POST['series'];

    // Format the date to "MM/DD/YYYY"
    $formattedIssue = date('m/d/Y', strtotime($issue)); // Convert to desired format

    // Check if a new series is being added
    if ($series === 'new' && !empty($_POST['newSeries'])) {
        $series = $_POST['newSeries']; // Use the new series name
    }

    // Define target directory for uploads
    $target_dir = "comic_images/";
    $extension = pathinfo($_FILES["one_file"]["name"], PATHINFO_EXTENSION);
    $filename = uniqid('comic_', true) . '.' . $extension; // Unique filename
    $target_file = $target_dir . $filename;

    // Validate file upload
    if (move_uploaded_file($_FILES["one_file"]["tmp_name"], $target_file)) {
        // Prepare new issue data
        $newIssue = [
            'title' => $title,
            'date_issued' => $formattedIssue, // Use formatted date here
            'picture' => $filename
        ];

        // Check if the series exists and add the new issue
        if (array_key_exists($series, $comicsData['comics'])) {
            $comicsData['comics'][$series][] = $newIssue;
            $message = "The file " . basename($_FILES["one_file"]["name"]) . " has been uploaded and the new issue has been added.";
        } else {
            // Create new series if it doesn't exist
            $comicsData['comics'][$series] = [$newIssue];
            $message = "New series created and the file " . basename($_FILES["one_file"]["name"]) . " has been uploaded.";
        }

        // Save updated JSON data
        file_put_contents('comics.json', json_encode($comicsData, JSON_PRETTY_PRINT));
    } else {
        $message = "There was an error uploading your file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Comic Issue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Add New Comic Issue</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="create.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="series" class="form-label">Select Series</label>
                <select class="form-select" id="series" name="series" required>
                    <option value="">Select a series</option>
                    <?php foreach ($comicsData['comics'] as $seriesName => $issues): ?>
                        <option value="<?php echo htmlspecialchars($seriesName); ?>"><?php echo htmlspecialchars($seriesName); ?></option>
                    <?php endforeach; ?>
                    <option value="new">New Series</option>
                </select>
            </div>
            <div class="mb-3" id="newSeriesContainer" style="display:none;">
                <label for="newSeries" class="form-label">New Series Name</label>
                <input type="text" class="form-control" id="newSeries" name="newSeries" placeholder="Enter new series name">
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Issue # (example input: issue 1)</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="issue" class="form-label">Issue Date</label>
                <input type="date" class="form-control" id="issue" name="issue" required>
            </div>
            <div class="mb-3">
                <label for="one_file" class="form-label">Upload Comic Cover Image</label>
                <input type="file" class="form-control" id="one_file" name="one_file" accept="image/*" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Add Issue</button>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show new series input when "New Series" is selected
        const seriesSelect = document.getElementById('series');
        const newSeriesContainer = document.getElementById('newSeriesContainer');

        seriesSelect.addEventListener('change', function() {
            if (this.value === 'new') {
                newSeriesContainer.style.display = 'block';
            } else {
                newSeriesContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>
