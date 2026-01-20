<?php
session_start();
require 'connection.php'; // your database connection file

$connect = Connect();

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Simple validation
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password cannot be empty.";
        header("Location: register_user.php");
        exit;
    }

    // Check if username already exists
    $stmt = $connect->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "Username already exists.";
        header("Location: register_user.php");
        exit;
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $connect->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    if ($stmt->execute([$username, $hashed_password])) {
        $_SESSION['success'] = "User registered successfully!";
        header("Location: login.php"); // go to login page
        exit;
    } else {
        $_SESSION['error'] = "Error registering user.";
        header("Location: register_user.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register User</title>
</head>
<body>

<h2>Register New User</h2>

<?php if (isset($_SESSION['error'])): ?>
    <p style="color:red">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </p>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <p style="color:green">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    </p>
<?php endif; ?>

<form method="POST" action="register_user.php">
    <input type="text" name="username" placeholder="Username" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Register</button>
</form>

</body>
</html>
