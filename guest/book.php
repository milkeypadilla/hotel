<?php

include '../connection.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guest') {
    header("Location: ../login.php");
    exit;
}

$room_id = isset($_REQUEST['room']) ? (int)$_REQUEST['room'] : 0;
$check_in = isset($_REQUEST['check_in']) ? mysqli_real_escape_string($conn, $_REQUEST['check_in']) : "";
$check_out = isset($_REQUEST['check_out']) ? mysqli_real_escape_string($conn, $_REQUEST['check_out']) : "";
$guests = isset($_REQUEST['guests']) ? (int)$_REQUEST['guests'] : 1;

$result = mysqli_query($conn, "SELECT * FROM rooms WHERE id = $room_id");
$room = mysqli_fetch_assoc($result);

$error = "";
if (!$room) {
    $error = "That room does not exist.";
} elseif (strtotime($check_in) === false || strtotime($check_out) === false) {
    $error = "The dates are not valid.";
} elseif ($check_in < date('Y-m-d')) {
    $error = "Check-in cannot be in the past.";
} elseif ($check_out <= $check_in) {
    $error = "Check-out must be after check-in.";
} elseif ($guests < 1 || $guests > $room['capacity']) {
    $error = "This room fits 1 to " . $room['capacity'] . " guest(s).";
} else {
    $sql = "SELECT id FROM reservations
            WHERE room_id = $room_id AND status IN ('pending','confirmed')
            AND check_in < '$check_out' AND check_out > '$check_in'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        $error = "Sorry, this room was just booked for those dates. Please search again.";
    }
}

if (isset($_POST['book']) && $error == "") {
    $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
    $total = $nights * $room['price_per_night'];
    $guest_id = (int)$_SESSION['id'];

    $sql = "INSERT INTO reservations (guest_id, room_id, check_in, check_out, guests, total_price, status)
            VALUES ($guest_id, $room_id, '$check_in', '$check_out', $guests, $total, 'pending')";
    mysqli_query($conn, $sql);
    header("Location: reservations.php?booked=1");
    exit;
}

$title = "Confirm booking";
$base = "../";
include '../header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-9 col-lg-6">
    <a href="search.php" class="small text-decoration-none">&larr; Back to search</a>
    <div class="page-head mt-2">
      <div><h1>Confirm your booking</h1></div>
    </div>

    <?php if ($error != "") { ?>
      <div class="alert alert-danger py-2"><?= h($error) ?></div>
      <a href="search.php" class="btn btn-primary">Search again</a>
    <?php } else {
      $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;
    ?>
      <div class="card">
        <div class="card-body p-4">
          <dl class="summary mb-0">
            <dt>Room</dt>
            <dd><?= h($room['room_no']) ?> - <?= h($room['room_type']) ?></dd>
            <dt>Stay</dt>
            <dd><?= date('M j, Y', strtotime($check_in)) ?> to <?= date('M j, Y', strtotime($check_out)) ?> (<?= $nights ?> night(s))</dd>
            <dt>Guests</dt>
            <dd><?= $guests ?></dd>
            <dt>Price</dt>
            <dd>&#8369;<?= number_format($room['price_per_night'], 2) ?> per night</dd>
            <dt>Total</dt>
            <dd class="total">&#8369;<?= number_format($room['price_per_night'] * $nights, 2) ?></dd>
          </dl>
          <form method="POST" class="mt-3">
            <input type="hidden" name="room" value="<?= $room_id ?>">
            <input type="hidden" name="check_in" value="<?= h($check_in) ?>">
            <input type="hidden" name="check_out" value="<?= h($check_out) ?>">
            <input type="hidden" name="guests" value="<?= $guests ?>">
            <button type="submit" name="book" class="btn btn-primary px-4">Confirm booking</button>
            <a href="search.php" class="btn btn-outline-secondary">Cancel</a>
          </form>
          <p class="text-muted small mt-3 mb-0">Your reservation stays "pending" until the hotel confirms it.</p>
        </div>
      </div>
    <?php } ?>
  </div>
</div>
<?php include '../footer.php'; ?>
