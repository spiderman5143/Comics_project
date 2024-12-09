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

// Handle deletion for series (Form 1)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_series'])) {
    $series = $_POST['series'] ?? null;
    $delUsername = $_POST['username'] ?? null;

    if ($series) {
        try {
            // Check if the user is authorized to delete this series
            if ($isAdmin) {
                // Admin needs to specify the user to delete the series
                if ($delUsername) {
                    $userQuery = "SELECT user_id FROM users WHERE username = ?";
                    $stmt = $pdo->prepare($userQuery);
                    $stmt->execute([$delUsername]);
                    $userId = $stmt->fetchColumn();

                    $deleteQuery = "DELETE FROM comics WHERE series = ? AND user_id = ?";
                    $stmt = $pdo->prepare($deleteQuery);
                    $stmt->execute([$series, $userId]);
                    $message = "Series deleted successfully.";
                } else {
                    $error = "Username is required to delete a series.";
                }
            } else {
                // Users can delete series they own
                $deleteQuery = "DELETE FROM comics WHERE series = ? AND user_id = (SELECT user_id FROM users WHERE username = ?)";
                $stmt = $pdo->prepare($deleteQuery);
                $stmt->execute([$series, $username]);
                $message = "Series deleted successfully.";
            }
        } catch (PDOException $e) {
            $error = "Error deleting series: " . $e->getMessage();
        }
    } else {
        $error = "Please select a series to delete.";
    }
}

// Handle deletion for issue (Form 2)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_issue'])) {
    $series = $_POST['series'] ?? null;
    $issue = $_POST['issue'] ?? null;
    $delUsername = $_POST['username'] ?? null;

    if ($series && $issue) {
        try {
            // Check if the user is authorized to delete this issue
            if ($isAdmin) {
                // Admin needs to specify the user to delete the issue
                if ($delUsername) {
                    $userQuery = "SELECT user_id FROM users WHERE username = ?";
                    $stmt = $pdo->prepare($userQuery);
                    $stmt->execute([$delUsername]);
                    $userId = $stmt->fetchColumn();

                    $deleteQuery = "DELETE FROM comics WHERE series = ? AND title = ? AND user_id = ?";
                    $stmt = $pdo->prepare($deleteQuery);
                    $stmt->execute([$series, $issue, $userId]);
                    $message = "Issue deleted successfully.";
                } else {
                    $error = "Username is required to delete an issue.";
                }
            } else {
                // Users can delete issues they own
                $deleteQuery = "DELETE FROM comics WHERE series = ? AND title = ? AND user_id = (SELECT user_id FROM users WHERE username = ?)";
                $stmt = $pdo->prepare($deleteQuery);
                $stmt->execute([$series, $issue, $username]);
                $message = "Issue deleted successfully.";
            }
        } catch (PDOException $e) {
            $error = "Error deleting issue: " . $e->getMessage();
        }
    } else {
        $error = "Please select a series and issue to delete.";
    }
}

// Fetch series for the dropdown
$seriesOptions = [];
try {
    if ($isAdmin) {
        $query = "SELECT DISTINCT series, (SELECT username FROM users WHERE user_id = comics.user_id) AS username FROM comics";
        $stmt = $pdo->query($query);
    } else {
        $query = "SELECT DISTINCT series, (SELECT username FROM users WHERE user_id = comics.user_id) AS username FROM comics WHERE user_id = (SELECT user_id FROM users WHERE username = ?)";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$username]);
    }
    $seriesOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching series: " . $e->getMessage();
}

// Fetch issues for the selected series via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && isset($_POST['series']) && isset($_POST['username'])) {
    $series = $_POST['series'];
    $username = $_POST['username'];

    try {
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
    <title>Delete Comics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<div class="container mt-5">
    <h1>Delete Comics</h1>

    <!-- Display messages only after the form is submitted -->
    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- Form 1: Delete Series -->
    <form method="POST" id="deleteSeriesForm">
        <h3>Delete a Series</h3>
        <div class="mb-3">
            <label for="series" class="form-label">Select a Series</label>
            <select name="series" id="series" class="form-select" required>
                <option value="">Select a series</option>
                <?php foreach ($seriesOptions as $option): ?>
                    <option value="<?php echo htmlspecialchars($option['series']); ?>"
                        data-username="<?php echo htmlspecialchars($option['username']); ?>"
                        <?php echo isset($series) && $series === $option['series'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($option['series']) . ($isAdmin ? " (owner: {$option['username']})" : ""); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($isAdmin): ?>
                <input type="hidden" name="username" id="usernameHidden" value="">
            <?php endif; ?>
        </div>
        <button type="submit" name="delete_series" class="btn btn-danger">Delete Series</button>
    </form>

    <!-- Form 2: Delete Issue -->
    <form method="POST" id="deleteIssueForm" class="mt-4">
        <h3>Delete a Single Issue</h3>
        <div class="mb-3">
            <label for="seriesSelect" class="form-label">Select a Series</label>
            <select name="series" id="seriesSelect" class="form-select" required>
                <option value="">Select a series</option>
                <?php foreach ($seriesOptions as $option): ?>
                    <option value="<?php echo htmlspecialchars($option['series']); ?>"
                        data-username="<?php echo htmlspecialchars($option['username']); ?>"
                        <?php echo isset($series) && $series === $option['series'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($option['series']) . ($isAdmin ? " (owner: {$option['username']})" : ""); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="issueSelect" class="form-label">Select an Issue</label>
            <select name="issue" id="issueSelect" class="form-select" required>
                <option value="">Select an issue</option>
            </select>
        </div>
        <?php if ($isAdmin): ?>
            <input type="hidden" name="username" id="usernameHiddenIssue" value="">
        <?php endif; ?>
        <button type="submit" name="delete_issue" class="btn btn-danger">Delete Issue</button>
    </form>

    <a href="index.php" class="btn btn-secondary mt-3">Back to Home</a>
</div>

<script>
$(document).ready(function() {
    $('#series').change(function() {
        var username = $(this).find(':selected').data('username');
        $('#usernameHidden').val(username);
    });

    $('#seriesSelect').change(function() {
        var series = $(this).val();
        var username = $(this).find(':selected').data('username');
        $('#usernameHiddenIssue').val(username);

        if (series && username) {
            $.ajax({
                type: 'POST',
                url: '',
                data: { ajax: 1, series: series, username: username },
                success: function(response) {
                    $('#issueSelect').html(response);
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
