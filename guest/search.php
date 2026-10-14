<?php

include '../connection.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guest') {
    header("Location: ../login.php");
    exit;
}


$check_in = date('Y-m-d');
$check_out = date('Y-m-d', strtotime('+1 day'));
$guests = 1;

if (isset($_GET['check_in'])) {
    $check_in = mysqli_real_escape_string($conn, $_GET['check_in']);
    $check_out = mysqli_real_escape_string($conn, $_GET['check_out']);
    $guests = (int)$_GET['guests'];
}

$error = "";
$rooms = null;
$nights = 0;

if (strtotime($check_in) === false || strtotime($check_out) === false) {
    $error = "Please choose valid dates.";
} elseif ($check_in < date('Y-m-d')) {
    $error = "Check-in cannot be in the past.";
} elseif ($check_out <= $check_in) {
    $error = "Check-out must be after check-in.";
} elseif ($guests < 1) {
    $error = "Enter at least 1 guest.";
} else {
    $nights = (strtotime($check_out) - strtotime($check_in)) / 86400;

    $sql = "SELECT * FROM rooms
            WHERE capacity >= $guests
            AND id NOT IN (
                SELECT room_id FROM reservations
                WHERE status IN ('pending','confirmed')
                AND check_in < '$check_out' AND check_out > '$check_in'
            )
            ORDER BY price_per_night";
    $rooms = mysqli_query($conn, $sql);
}

$title = "Find a room";
$base = "../";
include '../header.php';
?>
<div class="page-head">
  <div>
    <h1>Find a room</h1>
    <p>Choose your dates and see which rooms are free.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-body">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-6 col-md-3">
        <label class="form-label">Check-in</label>
        <input type="date" name="check_in" class="form-control" min="<?= date('Y-m-d') ?>" value="<?= h($check_in) ?>" required>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Check-out</label>
        <input type="date" name="check_out" class="form-control" value="<?= h($check_out) ?>" required>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Guests</label>
        <input type="number" name="guests" class="form-control" min="1" value="<?= $guests ?>" required>
      </div>
      <div class="col-6 col-md-3">
        <button type="submit" class="btn btn-primary w-100">Search rooms</button>
      </div>
    </form>
  </div>
</div>

<?php if ($error != "") { ?>
  <div class="alert alert-danger"><?= h($error) ?></div>
<?php } else { ?>
  <h2 class="h5 mb-3">
    <?= mysqli_num_rows($rooms) ?> room(s) free, <?= date('M j', strtotime($check_in)) ?> to <?= date('M j, Y', strtotime($check_out)) ?>
    <span class="text-muted fw-normal">(<?= $nights ?> night(s))</span>
  </h2>

  <?php while ($row = mysqli_fetch_assoc($rooms)) { ?>
    <div class="card room-card">
      <div class="d-flex align-items-center gap-3 flex-grow-1">
        <div class="plate"><small>Room</small><strong><?= h($row['room_no']) ?></strong></div>
        <div class="info">
          <h2><?= h($row['room_type']) ?></h2>
          <div class="text-muted"><?= h($row['description']) ?></div>
          <small class="text-muted">Sleeps <?= $row['capacity'] ?></small>
        </div>
      </div>
      <div class="price">
        <div class="amount">&#8369;<?= number_format($row['price_per_night'] * $nights, 2) ?></div>
        <small class="text-muted">&#8369;<?= number_format($row['price_per_night'], 2) ?> per night</small>
      </div>
      <a class="btn btn-primary" href="book.php?room=<?= $row['id'] ?>&check_in=<?= $check_in ?>&check_out=<?= $check_out ?>&guests=<?= $guests ?>">Book this room</a>
    </div>
  <?php } ?>

  <?php if (mysqli_num_rows($rooms) == 0) { ?>
    <div class="table-card p-4 text-center text-muted">No rooms are free for those dates. Try different dates or fewer guests.</div>
  <?php } ?>
<?php } ?>
<?php include '../footer.php'; ?>
