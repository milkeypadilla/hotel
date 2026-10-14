<?php

include '../connection.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";
$error = "";

$id = 0;
$name = "";
$email = "";
$phone = "";

if (isset($_POST['delete'])) {
    $delete_id = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM users WHERE id = $delete_id AND role = 'guest'");
    $message = "Guest deleted.";
}

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id = $edit_id AND role = 'guest'");
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $id = $row['id'];
        $name = $row['name'];
        $email = $row['email'];
        $phone = $row['phone'];
    }
}

if (isset($_POST['save'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];

    $name_safe = mysqli_real_escape_string($conn, $name);
    $email_safe = mysqli_real_escape_string($conn, $email);
    $phone_safe = mysqli_real_escape_string($conn, $phone);

    if ($name == "" || $phone == "") {
        $error = "Name and phone are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Enter a valid email.";
    } elseif (strlen($phone) != 11 || substr($phone, 0, 2) != "09" || !ctype_digit($phone)) {
        $error = "Phone number must start with 09 and have exactly 11 digits.";
    } elseif ($id == 0 && strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($id > 0 && $password != "" && strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $result = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email_safe' AND id <> $id");
        if (mysqli_num_rows($result) > 0) {
            $error = "That email is already used.";
        } elseif ($id > 0) {
            mysqli_query($conn, "UPDATE users SET name='$name_safe', email='$email_safe', phone='$phone_safe' WHERE id=$id AND role='guest'");
     
            if ($password != "") {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                mysqli_query($conn, "UPDATE users SET password='$hash' WHERE id=$id");
            }
            $message = "Guest updated.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, role, phone)
                    VALUES ('$name_safe', '$email_safe', '$hash', 'guest', '$phone_safe')";
            mysqli_query($conn, $sql);
            $message = "Guest added.";
        }
        if ($error == "") {
            $id = 0; $name = ""; $email = ""; $phone = "";
        }
    }
}

$search = isset($_GET['q']) ? trim($_GET['q']) : "";
$q = mysqli_real_escape_string($conn, $search);
$sql = "SELECT u.*, (SELECT COUNT(*) FROM reservations x WHERE x.guest_id = u.id) AS stays
        FROM users u
        WHERE u.role = 'guest' AND (u.name LIKE '%$q%' OR u.email LIKE '%$q%' OR u.phone LIKE '%$q%')
        ORDER BY u.name";
$guests = mysqli_query($conn, $sql);

$title = "Guests";
$base = "../";
include '../header.php';
?>
<div class="page-head">
  <div>
    <h1>Guests</h1>
    <p>Guest accounts. Guests can also register by themselves.</p>
  </div>
</div>

<?php if ($message != "") { ?><div class="alert alert-success py-2"><?= h($message) ?></div><?php } ?>
<?php if ($error != "") { ?><div class="alert alert-danger py-2"><?= h($error) ?></div><?php } ?>

<div class="card mb-4">
  <div class="card-header"><?php echo ($id > 0) ? "Edit guest" : "Add a guest"; ?></div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="id" value="<?= $id ?>">
      <div class="col-md-3">
        <label class="form-label">Full name</label>
        <input type="text" name="name" class="form-control" value="<?= h($name) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="<?= h($email) ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" placeholder="09123456789" value="<?= h($phone) ?>"
               pattern="09[0-9]{9}" maxlength="11" inputmode="numeric"
               title="Must start with 09 and have exactly 11 digits" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Password <?php if ($id > 0) echo '<span class="text-muted fw-normal">(blank = keep)</span>'; ?></label>
        <input type="password" name="password" class="form-control" <?php if ($id == 0) echo 'required'; ?>>
      </div>
      <div class="col-12 d-flex gap-2">
        <button type="submit" name="save" class="btn btn-primary">Save guest</button>
        <?php if ($id > 0) { ?><a href="guests.php" class="btn btn-outline-secondary">Cancel</a><?php } ?>
      </div>
    </form>
  </div>
</div>

<form class="d-flex gap-2 mb-3" method="GET">
  <input class="form-control" type="search" name="q" placeholder="Search name, email, or phone" value="<?= h($search) ?>">
  <button type="submit" class="btn btn-primary">Search</button>
  <?php if ($search != "") { ?><a href="guests.php" class="btn btn-outline-secondary">Clear</a><?php } ?>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Guest</th><th>Phone</th><th>Reservations</th><th></th></tr></thead>
      <tbody>
      <?php while ($row = mysqli_fetch_assoc($guests)) { ?>
        <tr>
          <td>
            <div class="person">
              <span class="avatar"><?= h(strtoupper(substr($row['name'], 0, 1))) ?></span>
              <div><?= h($row['name']) ?><br><small><?= h($row['email']) ?></small></div>
            </div>
          </td>
          <td><?= h($row['phone']) ?></td>
          <td><?= $row['stays'] ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="guests.php?edit=<?= $row['id'] ?>">Edit</a>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this guest and all their reservations?')">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
  <?php if (mysqli_num_rows($guests) == 0) { ?>
    <div class="p-4 text-center text-muted">No guests found.</div>
  <?php } ?>
</div>
<?php include '../footer.php'; ?>
