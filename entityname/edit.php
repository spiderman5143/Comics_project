<?php
session_start(); // Start the session

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Store the requested page URL in the session
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Current page URL
    header("Location: login.php"); // Redirect to login
    exit;
}

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page
    exit;
}

// Load existing JSON data
$jsonData = file_get_contents('comics.json');
$comicsData = json_decode($jsonData, true);

// Initialize message variable
$message = '';

// Handle series title edit submission
if (isset($_POST['edit_titles'])) {
    $oldTitle = $_POST['oldTitle'];
    $newTitle = $_POST['newTitle'];

    // Update the comic series title
    if (array_key_exists($oldTitle, $comicsData['comics'])) {
        $comicsData['comics'][$newTitle] = $comicsData['comics'][$oldTitle];
        unset($comicsData['comics'][$oldTitle]);
        $message = "Series title updated from '{$oldTitle}' to '{$newTitle}'.";
    } else {
        $message = "Series '{$oldTitle}' not found.";
    }

    // Save updated JSON data
    file_put_contents('comics.json', json_encode($comicsData, JSON_PRETTY_PRINT));
}

// Handle comic issue edit submission
if (isset($_POST['edit_issues'])) {
    $series = $_POST['issue_series'];
    $issueTitle = $_POST['issueTitle'];
    $newIssueTitle = $_POST['newIssueTitle'];
    $issueDate = $_POST['issueDate']; // Keep this as is; we'll format it before saving
    $issuePicture = $_FILES['one_file']['name']; // Get the uploaded file name

    // Update comic issue if a series is selected
    if (!empty($series) && array_key_exists($series, $comicsData['comics'])) {
        foreach ($comicsData['comics'][$series] as &$issue) {
            if ($issue['title'] === $issueTitle) {
                if (!empty($newIssueTitle)) {
                    $issue['title'] = $newIssueTitle; // Update issue title if provided
                }
                if (!empty($issueDate)) {
                    // Format the date as MM/DD/YYYY
                    $dateParts = explode('-', $issueDate);
                    if (count($dateParts) === 3) {
                        $issue['date_issued'] = $dateParts[1] . '/' . $dateParts[2] . '/' . $dateParts[0]; // Convert to MM/DD/YYYY
                    }
                }
                // Check if a new picture is uploaded
                if (!empty($_FILES['one_file']['tmp_name'])) {
                    // Delete the old image file if it exists
                    if (file_exists("comic_images/" . $issue['picture'])) {
                        unlink("comic_images/" . $issue['picture']); // Remove the old image file
                    }
                    // Upload the new image file with a unique filename
                    $targetDir = "comic_images/"; // Define your image upload directory
                    $extension = pathinfo($_FILES['one_file']['name'], PATHINFO_EXTENSION);
                    $uniqueFilename = uniqid('comic_', true) . '.' . $extension; // Unique filename
                    $targetFile = $targetDir . $uniqueFilename;

                    if (move_uploaded_file($_FILES['one_file']['tmp_name'], $targetFile)) {
                        $issue['picture'] = $uniqueFilename; // Update picture path
                    } else {
                        $message = "There was an error uploading the new image.";
                    }
                }
                $message = "Comic issue '{$issueTitle}' updated successfully.";
                break;
            }
        }
    }

    // Save updated JSON data
    file_put_contents('comics.json', json_encode($comicsData, JSON_PRETTY_PRINT));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Comics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Edit Comics</h1>

        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Edit Titles Section -->
        <h2>Edit Titles</h2>
        <form action="edit.php" method="POST">
            <div class="mb-3">
                <label for="oldTitle" class="form-label">Select Series to Edit</label>
                <select class="form-select" id="oldTitle" name="oldTitle" required>
                    <option value="">Select a series</option>
                    <?php foreach (array_keys($comicsData['comics']) as $series): ?>
                        <option value="<?php echo htmlspecialchars($series); ?>"><?php echo htmlspecialchars($series); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="newTitle" class="form-label">New Series Title</label>
                <input type="text" class="form-control" id="newTitle" name="newTitle" required>
            </div>
            <button type="submit" name="edit_titles" class="btn btn-primary">Update Title</button>
        </form>

        <!-- Edit Comic Issues Section -->
        <h2 class="mt-4">Edit Comic Issues</h2>
        <form action="edit.php" method="POST" enctype="multipart/form-data"> <!-- Added enctype for file upload -->
            <div class="mb-3">
                <label for="issue_series" class="form-label">Select Series</label>
                <select class="form-select" id="issue_series" name="issue_series" required onchange="updateIssueDropdown(this.value)">
                    <option value="">Select a series</option>
                    <?php foreach (array_keys($comicsData['comics']) as $series): ?>
                        <option value="<?php echo htmlspecialchars($series); ?>"><?php echo htmlspecialchars($series); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="issueTitle" class="form-label">Select Issue to Edit</label>
                <select class="form-select" id="issueTitle" name="issueTitle" required>
                    <option value="">Select an issue</option>
                    <?php
                    if (!empty($_POST['issue_series'])) {
                        $selectedSeries = $_POST['issue_series'];
                        foreach ($comicsData['comics'][$selectedSeries] as $issue) {
                            echo '<option value="' . htmlspecialchars($issue['title']) . '">' . htmlspecialchars($issue['title']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="newIssueTitle" class="form-label">New Issue Title</label>
                <input type="text" class="form-control" id="newIssueTitle" name="newIssueTitle">
            </div>
            <div class="mb-3">
                <label for="issueDate" class="form-label">New Issue Date (MM/DD/YYYY)</label>
                <input type="date" class="form-control" id="issueDate" name="issueDate"> <!-- Removed required attribute -->
            </div>
            <div class="mb-3">
                <label for="one_file" class="form-label">Upload Comic Cover Image</label>
                <input type="file" class="form-control" id="one_file" name="one_file" accept="image/*">
            </div>
            <button type="submit" name="edit_issues" class="btn btn-primary">Update Issue</button>
        </form>
        
        <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Function to update issue dropdown based on selected series
        function updateIssueDropdown(series) {
            var issueDropdown = document.getElementById('issueTitle');
            issueDropdown.innerHTML = '<option value="">Select an issue</option>'; // Reset the options

            // Populate the issue dropdown with the selected series' issues
            <?php foreach ($comicsData['comics'] as $series => $issues): ?>
                if (series === "<?php echo $series; ?>") {
                    <?php foreach ($issues as $issue): ?>
                        issueDropdown.innerHTML += '<option value="<?php echo htmlspecialchars($issue['title']); ?>"><?php echo htmlspecialchars($issue['title']); ?></option>';
                    <?php endforeach; ?>
                }
            <?php endforeach; ?>
        }
    </script>
</body>
</html>
