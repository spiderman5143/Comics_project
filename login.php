<?php
session_start(); // Start the session

// Load users from JSON
$usersData = json_decode(file_get_contents('users.json'), true);

// Initialize message variable
$message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the username exists
    foreach ($usersData['users'] as $user) {
        if ($user['username'] === $username && $user['password'] === $password) {
            // Set session variable and redirect
            $_SESSION['username'] = $username;
            $_SESSION['loggedin'] = true; // Set logged in status
            header("Location: create.php"); // Redirect to create.php after login
            exit;
        }
    }
    $message = "Invalid username or password.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
</head>
<body>
    <h1>Login</h1>
    <?php if ($message): ?>
        <div style="color: red;"><?php echo $message; ?></div>
    <?php endif; ?>
    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
