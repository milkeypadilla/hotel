<?php
include 'connection.php';

$error = "";
$name = "";
$email = "";
$phone = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if ($name == "" || $phone == "") {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email.";
    } elseif (strlen($phone) != 11 || substr($phone, 0, 2) != "09" || !ctype_digit($phone)) {
        $error = "Phone number must start with 09 and have exactly 11 digits.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($password != $confirm) {
        $error = "Passwords do not match.";
    } else {

        $name_safe = mysqli_real_escape_string($conn, $name);
        $email_safe = mysqli_real_escape_string($conn, $email);
        $phone_safe = mysqli_real_escape_string($conn, $phone);

        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email_safe'");
        if (mysqli_num_rows($check) > 0) {
            $error = "That email is already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role, phone)
                    VALUES ('$name_safe', '$email_safe', '$hash', 'guest', '$phone_safe')";
            mysqli_query($conn, $sql);
            header("Location: login.php?registered=1");
            exit;
        }
    }
}

$title = "Register";
$heading = "Create your account";
$sub = "Sign up as a guest to book rooms.";
include 'auth_top.php';
?>
      <?php if ($error != "") { ?>
        <div class="alert alert-danger py-2"><?= h($error) ?></div>
      <?php } ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Full name</label>
          <input type="text" name="name" class="form-control" value="<?= h($name) ?>" required autofocus>
        </div>
        <div class="row">
          <div class="col-sm-6 mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= h($email) ?>" required>
          </div>
          <div class="col-sm-6 mb-3">
            <label class="form-label">Phone number</label>
            <input type="text" name="phone" class="form-control" placeholder="09123456789" value="<?= h($phone) ?>"
                   pattern="09[0-9]{9}" maxlength="11" inputmode="numeric"
                   title="Must start with 09 and have exactly 11 digits" required>
            <div class="form-text">Starts with 09, 11 digits.</div>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" minlength="6" required>
          <div class="form-text">At least 6 characters.</div>
        </div>
        <div class="mb-4">
          <label class="form-label">Confirm password</label>
          <input type="password" name="confirm" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary w-100 py-2">Create account</button>
      </form>

      <p class="mt-4 mb-0 text-muted">Already have an account? <a href="login.php" class="fw-semibold">Log in</a></p>
<?php include 'auth_bottom.php'; ?>
