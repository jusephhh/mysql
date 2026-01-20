<?php
session_start();
require 'connection.php';

$connect = Connect();

$username = $_POST['username'];
$password = $_POST['password'];

$stmt = $connect->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user->password)) {
    // LOGIN SUCCESS
    $_SESSION['username'] = $user->username;

    header("Location: employee.php"); // ✅ go to employees
    exit;
}

// LOGIN FAILED
$_SESSION['error'] = "Invalid username or password";
header("Location: login.php");
exit;
