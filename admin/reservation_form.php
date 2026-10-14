<?php
include '../connection.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$error = "";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$guest_id = "";
$room_id = "";
$guests = 1;
$check_in = date('Y-m-d');
$check_out = date('Y-m-d', strtotime('+1 day'));
$status = "confirmed";

if ($id > 0 && !isset($_POST['save'])) {
    $result = mysqli_query($conn, "SELECT * FROM reservations WHERE id = $id");
    $row = mysqli_fetch_assoc($result);
    if (!$row) {
        header("Location: reservations.php");
        exit;
    }
    $guest_id = $row['guest_id'];
    $room_id = $row['room_id'];
    $guests = $row['guests'];
    $check_in = $row['check_in'];
    $check_out = $row['check_out'];
    $status = $row['status'];
}

if (isset($_POST['save'])) {
    $guest_id = (int)$_POST['guest_id'];
    $room_id = (int)$_POST['room_id'];
    $guests = (int)$_POST['guests'];
    $check_in = mysqli_real_escape_string($conn, $_POST['check_in']);
    $check_out = mysqli_real_escape_string($conn, $_POST['check_out']);
    $status = $_POST['status'];

    $result = mysqli_query($conn, "SELECT * FROM rooms WHERE id = $room_id");
    $room = mysqli_fetch_assoc($result);
    $result = mysqli_query($conn, "SELECT id FROM users WHERE id = $guest_id AND role = 'guest'");
    $guest_found = mysqli_num_rows($result);

    if ($guest_found == 0) {
        $error = "Choose a guest.";
    } elseif (!$room) {
        $error = "Choose a room.";
    } elseif (strtotime($check_in) === false || strtotime($check_out) === false) {
        $error = "Enter a valid check-in and check-out date.";
    } elseif ($check_out <= $check_in) {
        $error = "Check-out must be after check-in.";
    } elseif ($guests < 1 || $guests > $room['capacity']) {
        $error = "This room fits 1 to " . $room['capacity'] . " guest(s).";
    } elseif (!in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
        $error = "Choose a status.";
    } elseif ($status == 'pending' || $status == 'confirmed') {
        $sql = "SELECT id FROM reservations
                WHERE room_id = $room_id AND id <> $id AND status IN ('pending','confirmed')
                AND check_in < '$check_out' AND check_out > '$check_in'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            $error = "That room is already booked for some of those dates.";
        }
    }
    if ($error == "") {
        $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
        $total = $nights * $room['price_per_night'];

        if ($id > 0) {
            $sql = "UPDATE reservations SET guest_id=$guest_id, room_id=$room_id, check_in='$check_in',
                    check_out='$check_out', guests=$guests, total_price=$total, status='$status' WHERE id=$id";
        } else {
            $sql = "INSERT INTO reservations (guest_id, room_id, check_in, check_out, guests, total_price, status)
                    VALUES ($guest_id, $room_id, '$check_in', '$check_out', $guests, $total, '$status')";
        }
        mysqli_query($conn, $sql);
        header("Location: reservations.php");
        exit;
    }
}
$guest_list = mysqli_query($conn, "SELECT id, name, email FROM users WHERE role = 'guest' ORDER BY name");
$room_list = mysqli_query($conn, "SELECT * FROM rooms ORDER BY room_no");

$title = ($id > 0) ? "Edit reservation" : "Add reservation";
$base = "../";
include '../header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-9 col-lg-7">
    <a href="reservations.php" class="small text-decoration-none">&larr; Back to reservations</a>
    <div class="page-head mt-2">
      <div><h1><?= h($title) ?></h1></div>
    </div>
    <div class="card">
      <div class="card-body p-4">
        <?php if ($error != "") { ?><div class="alert alert-danger py-2"><?= h($error) ?></div><?php } ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Guest</label>
            <select name="guest_id" class="form-select" required>
              <option value="">Choose a guest</option>
              <?php while ($g = mysqli_fetch_assoc($guest_list)) { ?>
                <option value="<?= $g['id'] ?>" <?php if ($guest_id == $g['id']) echo 'selected'; ?>><?= h($g['name']) ?> (<?= h($g['email']) ?>)</option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Room</label>
            <select name="room_id" class="form-select" required>
              <option value="">Choose a room</option>
              <?php while ($r = mysqli_fetch_assoc($room_list)) { ?>
                <option value="<?= $r['id'] ?>" <?php if ($room_id == $r['id']) echo 'selected'; ?>>
                  <?= h($r['room_no']) ?> - <?= h($r['room_type']) ?> (sleeps <?= $r['capacity'] ?>) - <?= number_format($r['price_per_night'], 2) ?>/night
                </option>
              <?php } ?>
            </select>
          </div>
          <div class="row">
            <div class="col-sm-6 mb-3">
              <label class="form-label">Check-in</label>
              <input type="date" name="check_in" class="form-control" value="<?= h($check_in) ?>" required>
            </div>
            <div class="col-sm-6 mb-3">
              <label class="form-label">Check-out</label>
              <input type="date" name="check_out" class="form-control" value="<?= h($check_out) ?>" required>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-6 mb-4">
              <label class="form-label">Number of guests</label>
              <input type="number" name="guests" class="form-control" min="1" value="<?= h($guests) ?>" required>
            </div>
            <div class="col-sm-6 mb-4">
              <label class="form-label">Status</label>
              <select name="status" class="form-select">
                <option value="pending" <?php if ($status == 'pending') echo 'selected'; ?>>Pending</option>
                <option value="confirmed" <?php if ($status == 'confirmed') echo 'selected'; ?>>Confirmed</option>
                <option value="completed" <?php if ($status == 'completed') echo 'selected'; ?>>Completed</option>
                <option value="cancelled" <?php if ($status == 'cancelled') echo 'selected'; ?>>Cancelled</option>
              </select>
            </div>
          </div>
          <p class="text-muted small">The total price is nights x the room's price per night.</p>
          <button type="submit" name="save" class="btn btn-primary px-4">Save reservation</button>
          <a href="reservations.php" class="btn btn-outline-secondary">Cancel</a>
        </form>
      </div>
    </div>
  </div>
</div>
<?php include '../footer.php'; ?>
