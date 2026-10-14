<?php
include 'connection.php';

if (isset($_SESSION['role'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$email = "";

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email'");
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Wrong email or password.";
    }
}

$title = "Login";
$heading = "Welcome back";
$sub = "Log in to book a room or manage reservations.";
include 'auth_top.php';
?>
      <?php if (isset($_GET['registered'])) { ?>
        <div class="alert alert-success py-2">Account created. You can log in now.</div>
      <?php } ?>
      <?php if ($error != "") { ?>
        <div class="alert alert-danger py-2"><?= h($error) ?></div>
      <?php } ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" placeholder="you@example.com" value="<?= h($email) ?>" required autofocus>
        </div>
        <div class="mb-4">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
          <div class="form-check mt-2">
  <input type="checkbox" class="form-check-input" id="showPassword">
  <label class="form-check-label" for="showPassword">Show password</label>
</div>
        </div>
        <button type="submit" name="login" class="btn btn-primary w-100 py-2">Log in</button>
      </form>

      <p class="mt-4 mb-0 text-muted">New guest? <a href="register.php" class="fw-semibold">Create an account</a></p>
<?php include 'auth_bottom.php'; ?>
