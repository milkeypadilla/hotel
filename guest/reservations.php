<?php

include '../connection.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guest') {
    header("Location: ../login.php");
    exit;
}

$guest_id = (int)$_SESSION['id'];
$message = "";

if (isset($_GET['booked'])) {
    $message = "Reservation sent. The hotel will confirm it soon.";
}

if (isset($_POST['cancel'])) {
    $id = (int)$_POST['id'];
    $sql = "UPDATE reservations SET status='cancelled'
            WHERE id = $id AND guest_id = $guest_id AND status IN ('pending','confirmed') AND check_in >= CURDATE()";
    mysqli_query($conn, $sql);
    $message = "Reservation cancelled.";
}

$sql = "SELECT x.*, r.room_no, r.room_type, DATEDIFF(x.check_out, x.check_in) AS nights
        FROM reservations x
        JOIN rooms r ON r.id = x.room_id
        WHERE x.guest_id = $guest_id
        ORDER BY x.check_in DESC";
$reservations = mysqli_query($conn, $sql);

$title = "My reservations";
$base = "../";
include '../header.php';
?>
<div class="page-head">
  <div>
    <h1>My reservations</h1>
    <p>Your bookings and their status.</p>
  </div>
  <a href="search.php" class="btn btn-primary">Book a room</a>
</div>

<?php if ($message != "") { ?><div class="alert alert-success py-2"><?= h($message) ?></div><?php } ?>

<?php if (mysqli_num_rows($reservations) == 0) { ?>
  <div class="table-card p-4 text-center text-muted">You have no reservations yet. Use "Book a room" to make one.</div>
<?php } else { ?>
<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Room</th><th>Stay</th><th>Guests</th><th>Total</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php while ($row = mysqli_fetch_assoc($reservations)) { ?>
        <tr>
          <td><strong><?= h($row['room_no']) ?></strong><br><small class="text-muted"><?= h($row['room_type']) ?></small></td>
          <td>
            <?= date('M j, Y', strtotime($row['check_in'])) ?> to <?= date('M j, Y', strtotime($row['check_out'])) ?><br>
            <small class="text-muted"><?= $row['nights'] ?> night(s)</small>
          </td>
          <td><?= $row['guests'] ?></td>
          <td>&#8369;<?= number_format($row['total_price'], 2) ?></td>
          <td><span class="badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td class="text-end">
            <?php if (($row['status'] == 'pending' || $row['status'] == 'confirmed') && $row['check_in'] >= date('Y-m-d')) { ?>
              <form method="POST" class="d-inline" onsubmit="return confirm('Cancel this reservation?')">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                <button type="submit" name="cancel" class="btn btn-sm btn-outline-danger">Cancel</button>
              </form>
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>
<?php include '../footer.php'; ?>
