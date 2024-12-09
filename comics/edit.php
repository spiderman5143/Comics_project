<?php
session_start();
require_once 'db.php'; // Database connection

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Determine if the user is an admin
$isAdmin = false;
try {
    $adminCheckQuery = "SELECT COUNT(*) FROM admin WHERE username = ?";
    $stmt = $pdo->prepare($adminCheckQuery);
    $stmt->execute([$username]);
    $isAdmin = $stmt->fetchColumn() > 0;
} catch (PDOException $e) {
    die("Error checking admin status: " . $e->getMessage());
}

// Initialize variables for the form
$message = '';
$error = '';
$series = '';
$issue = '';
$newTitle = '';

// Handle editing for series (Form 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_series'])) {
    $series = $_POST['series'] ?? null;
    $newTitle = $_POST['new_title'] ?? null;
    $editUsername = $isAdmin ? ($_POST['username'] ?? null) : $username; // Use logged-in username if not admin

    if ($series && $newTitle && $editUsername) {
        try {
            $userQuery = "SELECT user_id FROM users WHERE username = ?";
            $stmt = $pdo->prepare($userQuery);
            $stmt->execute([$editUsername]);
            $userId = $stmt->fetchColumn();

            $updateQuery = "UPDATE comics SET series = ? WHERE series = ? AND user_id = ?";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([$newTitle, $series, $userId]);
            $message = "Series updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating series: " . $e->getMessage();
        }
    } else {
        $error = "Please fill out all fields to edit the series.";
    }
}

// Handle editing for issue (Form 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_issue'])) {
    $series = $_POST['series'] ?? null;
    $issue = $_POST['issue'] ?? null;
    $newTitle = $_POST['new_title'] ?? null;
    $editUsername = $isAdmin ? ($_POST['username'] ?? null) : $username; // Use logged-in username if not admin

    if ($series && $issue && $newTitle && $editUsername) {
        try {
            $userQuery = "SELECT user_id FROM users WHERE username = ?";
            $stmt = $pdo->prepare($userQuery);
            $stmt->execute([$editUsername]);
            $userId = $stmt->fetchColumn();

            $updateQuery = "UPDATE comics SET title = ? WHERE series = ? AND title = ? AND user_id = ?";
            $stmt = $pdo->prepare($updateQuery);
            $stmt->execute([$newTitle, $series, $issue, $userId]);
            $message = "Issue updated successfully.";
        } catch (PDOException $e) {
            $error = "Error updating issue: " . $e->getMessage();
        }
    } else {
        $error = "Please fill out all fields to edit the issue.";
    }
}

// Fetch series for the dropdown
$seriesOptions = [];
try {
    if ($isAdmin) {
        $query = "SELECT DISTINCT series, (SELECT username FROM users WHERE user_id = comics.user_id) AS username FROM comics";
        $stmt = $pdo->query($query);
    } else {
        $query = "SELECT DISTINCT series FROM comics WHERE user_id = (SELECT user_id FROM users WHERE username = ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
    }
    $seriesOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching series: " . $e->getMessage();
}

// Fetch issues for the selected series via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && isset($_POST['series'])) {
    $series = $_POST['series'];
    $username = $isAdmin ? $_POST['username'] : $username;

    try {
        // Fetch the user_id for the selected username
        $userQuery = "SELECT user_id FROM users WHERE username = ?";
        $stmt = $pdo->prepare($userQuery);
        $stmt->execute([$username]);
        $userId = $stmt->fetchColumn();

        $issueQuery = "SELECT title FROM comics WHERE series = ? AND user_id = ?";
        $stmt = $pdo->prepare($issueQuery);
        $stmt->execute([$series, $userId]);
        
        $issues = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($issues as $issue) {
            echo '<option value="' . htmlspecialchars($issue['title']) . '">' . htmlspecialchars($issue['title']) . '</option>';
        }
    } catch (PDOException $e) {
        echo '<option value="">Error fetching issues</option>';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Comics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Edit Comics</h1>

    <!-- Display messages only after the form is submitted -->
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Form 1: Edit Series -->
    <form method="POST" id="editSeriesForm">
        <h3>Edit a Series</h3>
        <div class="mb-3">
            <label for="series" class="form-label">Select a Series</label>
            <select name="series" id="series" class="form-select" required>
                <option value="">Select a series</option>
                <?php foreach ($seriesOptions as $option): ?>
                    <option value="<?php echo htmlspecialchars($option['series']); ?>"
                        data-username="<?php echo $isAdmin ? htmlspecialchars($option['username']) : htmlspecialchars($username); ?>"
                        <?php echo isset($series) && $series === $option['series'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($option['series']) . ($isAdmin ? " (owner: {$option['username']})" : ""); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($isAdmin): ?>
                <input type="hidden" name="username" id="usernameHiddenSeries" value="">
            <?php endif; ?>
        </div>
        <div class="mb-3">
            <label for="new_title" class="form-label">New Title</label>
            <input type="text" name="new_title" id="new_title" class="form-control" value="<?php echo htmlspecialchars($newTitle); ?>" required>
        </div>
        <button type="submit" name="edit_series" class="btn btn-primary">Edit Series</button>
    </form>

    <!-- Form 2: Edit Issue -->
    <form method="POST" id="editIssueForm" class="mt-4">
        <h3>Edit an Issue</h3>
        <div class="mb-3">
            <label for="seriesSelect" class="form-label">Select a Series</label>
            <select id="seriesSelect" name="series" class="form-select" required>
                <option value="">Select a series</option>
                <?php foreach ($seriesOptions as $option): ?>
                    <option value="<?php echo htmlspecialchars($option['series']); ?>"
                        data-username="<?php echo $isAdmin ? htmlspecialchars($option['username']) : htmlspecialchars($username); ?>">
                        <?php echo htmlspecialchars($option['series']) . ($isAdmin ? " (owner: {$option['username']})" : ""); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($isAdmin): ?>
                <input type="hidden" name="username" id="usernameHiddenIssue" value="">
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label for="issueSelect" class="form-label">Select an Issue</label>
            <select name="issue" id="issueSelect" class="form-select" required>
                <option value="">Select an issue</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="new_title_issue" class="form-label">New Title</label>
            <input type="text" name="new_title" id="new_title_issue" class="form-control" value="<?php echo htmlspecialchars($newTitle); ?>" required>
        </div>
        <button type="submit" name="edit_issue" class="btn btn-primary">Edit Issue</button>
    </form>
    <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
</div>

<script>
$(document).ready(function() {
    $('#series').change(function() {
        var username = $(this).find(':selected').data('username');
        $('#usernameHiddenSeries').val(username);
    });

    $('#seriesSelect').change(function() {
        var series = $(this).val();
        var username = $(this).find(':selected').data('username') || '<?php echo htmlspecialchars($username); ?>';
        $('#usernameHiddenIssue').val(username);

        if (series && username) {
            $.ajax({
                type: 'POST',
                url: '',
                data: { ajax: 1, series: series, username: username },
                success: function(response) {
                    $('#issueSelect').html(response);
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error: ' + status + error);
                }
            });
        } else {
            $('#issueSelect').html('<option value="">Select an issue</option>');
        }
    });
});
</script>

</body>
</html>
