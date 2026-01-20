<?php
require 'connection.php';
$connect = Connect();

$username = 'admin';
$password = 'admin123'; // plaintext password
$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $connect->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->execute([$username, $hashed]);

echo "Test user created! Username: admin, Password: admin123";
?>
