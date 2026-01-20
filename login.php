<?php session_start(); ?>
<!DOCTYPE html>
<html>
<body>

<h2>Login</h2>

<?php if (isset($_SESSION['error'])): ?>
  <p style="color:red">
    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
  </p>
<?php endif; ?>

<form method="POST" action="login_process.php">
  <input type="text" name="username" placeholder="Username" required><br><br>
  <input type="password" name="password" placeholder="Password" required><br><br>
  <button type="submit">Login</button>
</form>

<br>

<!-- Register Button -->
<form action="register_user.php">
    <button type="submit">Register New User</button>
</form>


</body>
</html>
