<?php
session_start();
// Database connection
require_once 'db.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI']; // Store current URL for post-login redirection
    header('Location: login.php');
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

// Fetch user details including user_id
if ($isAdmin) {
    // Admin users are stored in the admin table, so no need to fetch from users table
    $userId = null;  // Admin doesn't need a specific user ID initially
} else {
    // Fetch user details including user_id
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    $userId = $user['user_id'];
}

// Fetch all users for admin user selection
if ($isAdmin) {
    $usersQuery = $pdo->query("SELECT username FROM users ORDER BY username ASC");
    $usersList = $usersQuery->fetchAll(PDO::FETCH_COLUMN);
}

$message = '';

// Handle AJAX request to fetch series for selected user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax']) && isset($_POST['username'])) {
    $selectedUsername = $_POST['username'];

    try {
        $stmt = $pdo->prepare("SELECT DISTINCT series FROM comics WHERE user_id = (SELECT user_id FROM users WHERE username = :username) ORDER BY series ASC");
        $stmt->execute(['username' => $selectedUsername]);
        $seriesList = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($seriesList as $series) {
            echo '<option value="' . htmlspecialchars($series) . '">' . htmlspecialchars($series) . '</option>';
        }
        echo '<option value="new">New Series</option>';
    } catch (PDOException $e) {
        echo '<option value="">Error fetching series</option>';
    }
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    $selectedUser = $_POST['selectedUser'] ?? '';  // For admins to select user
    $series = $_POST['series'] ?? '';
    $newSeries = $_POST['newSeries'] ?? '';
    $title = $_POST['title'] ?? '';
    $issueDate = $_POST['issue'] ?? '';
    $formattedDate = date('Y-m-d', strtotime($issueDate)); // Convert to database format

    // Determine the final series
    if (empty($series) && empty($newSeries)) {
        $message = "Please select an existing series or enter a new series name.";
    } elseif (!empty($series) || !empty($newSeries)) {
        $finalSeries = $series === 'new' ? $newSeries : $series;

        // Handle file upload
        $target_dir = "comic_images/";
        $extension = pathinfo($_FILES["one_file"]["name"], PATHINFO_EXTENSION);
        $filename = uniqid('comic_', true) . '.' . $extension; // Unique filename
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["one_file"]["tmp_name"], $target_file)) {
            // Fetch user_id for the selected user
            if ($isAdmin && !empty($selectedUser)) {
                $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username");
                $stmt->execute(['username' => $selectedUser]);
                $selectedUserId = $stmt->fetchColumn();
            }

            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO comics (user_id, series, title, issue_date, picture) VALUES (:user_id, :series, :title, :issue_date, :picture)");
            $stmt->execute([
                ':user_id' => $isAdmin ? $selectedUserId : $userId, // Admin uses selected user's ID, else use logged-in user's ID
                ':series' => $finalSeries,
                ':title' => $title,
                ':issue_date' => $formattedDate,
                ':picture' => $filename,
            ]);

            $message = "The file " . basename($_FILES["one_file"]["name"]) . " has been uploaded, and the new issue has been added.";
        } else {
            $message = "There was an error uploading your file.";
        }
    }
}

// Fetch series for the current user or selected user by admin
$seriesList = [];
if ($isAdmin && !empty($selectedUser)) {
    $stmt = $pdo->prepare("SELECT DISTINCT series FROM comics WHERE user_id = (SELECT user_id FROM users WHERE username = :username) ORDER BY series ASC");
    $stmt->execute(['username' => $selectedUser]);
    $seriesList = $stmt->fetchAll(PDO::FETCH_COLUMN);
} else {
    $stmt = $pdo->prepare("SELECT DISTINCT series FROM comics WHERE user_id = :user_id ORDER BY series ASC");
    $stmt->execute(['user_id' => $userId]);
    $seriesList = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Comic Issue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Add New Comic Issue</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <?php if ($isAdmin): ?>
                <div class="mb-3">
                    <label for="selectedUser" class="form-label">Select User</label>
                    <select class="form-select" id="selectedUser" name="selectedUser" required>
                        <option value="">Select a user</option>
                        <?php foreach ($usersList as $userName): ?>
                            <option value="<?php echo htmlspecialchars($userName); ?>"><?php echo htmlspecialchars($userName); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="series" class="form-label">Select Series</label>
                <select class="form-select" id="series" name="series" required>
                    <option value="">Select a series</option>
                    <?php foreach ($seriesList as $seriesName): ?>
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
                <label for="title" class="form-label">Issue Title</label>
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
        const seriesSelect = document.getElementById('series');
        const newSeriesContainer = document.getElementById('newSeriesContainer');

        seriesSelect.addEventListener('change', function() {
            newSeriesContainer.style.display = this.value === 'new' ? 'block' : 'none';
        });

        $(document).ready(function() {
        $('#selectedUser').change(function() {
            var selectedUser = $(this).val();
                
                // AJAX call to fetch series for the selected user
                $.ajax({
                    type: 'POST',
                    url: '', // Use the same page
                    data: { ajax: 1, username: selectedUser },
                    success: function(response) {
                        $('#series').html(response);
                        $('#newSeriesContainer').hide(); // Hide the new series container if any
                    },
                    error: function() {
                        $('#series').html('<option value="">Error fetching series</option>');
                    }
                });
            });
            
            $('#series').change(function() {
                var seriesValue = $(this).val();
                if (seriesValue === 'new') {
                    $('#newSeriesContainer').show();
                } else {
                    $('#newSeriesContainer').hide();
                }
            });
        });

    </script>
</body>
</html>
