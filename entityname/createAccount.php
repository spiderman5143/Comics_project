<?php
session_start(); // Start the session

// Load users from JSON
$usersFile = 'users.json';
$usersData = json_decode(file_get_contents($usersFile), true);

// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirmPassword) {
        $message = "Passwords do not match.";
    } else {
        // Check if the username already exists
        $usernameExists = false;
        foreach ($usersData['users'] as $user) {
            if ($user['username'] === $username) {
                $usernameExists = true;
                break;
            }
        }

        if ($usernameExists) {
            $message = "Username already exists.";
        } else {
            // Add the new user to the array
            $newUser = [
                'username' => $username,
                'password' => $password // In a real-world application, make sure to hash passwords
            ];

            $usersData['users'][] = $newUser;

            // Save the updated users data to the JSON file
            file_put_contents($usersFile, json_encode($usersData, JSON_PRETTY_PRINT));

            // Set session variables and redirect to login
            $_SESSION['username'] = $username;
            $_SESSION['loggedin'] = true;
            header("Location: login.php"); // Redirect to login page after account creation
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Create an Account</h1>

        <?php if ($message): ?>
            <div class="alert alert-danger"><?php echo $message; ?></div>
        <?php endif; ?>

        <form action="createAccount.php" method="POST" class="mt-4">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" id="username" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Create Account</button>
        </form>

        <div class="text-center mt-3">
            <a href="login.php">Already have an account? Login here</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
