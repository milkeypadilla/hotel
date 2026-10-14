<?php
include '../connection.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";

if (isset($_POST['action'])) {
    $id = (int)$_POST['id'];
    $action = $_POST['action'];

    if ($action == 'confirm') {
        mysqli_query($conn, "UPDATE reservations SET status='confirmed' WHERE id=$id AND status='pending'");
        $message = "Reservation confirmed.";
    } elseif ($action == 'complete') {
        mysqli_query($conn, "UPDATE reservations SET status='completed' WHERE id=$id AND status='confirmed'");
        $message = "Guest checked out.";
    } elseif ($action == 'cancel') {
        mysqli_query($conn, "UPDATE reservations SET status='cancelled' WHERE id=$id AND status IN ('pending','confirmed')");
        $message = "Reservation cancelled.";
    } elseif ($action == 'delete') {
        mysqli_query($conn, "DELETE FROM reservations WHERE id=$id");
        $message = "Reservation deleted.";
    }
}

$today = date('Y-m-d');
$month = date('Y-m');

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservations WHERE status='pending'");
$row = mysqli_fetch_assoc($result);
$pending = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservations WHERE status='confirmed' AND check_in = '$today'");
$row = mysqli_fetch_assoc($result);
$arriving = $row['total'];

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM reservations WHERE status='confirmed' AND check_in <= '$today' AND check_out > '$today'");
$row = mysqli_fetch_assoc($result);
$staying = $row['total'];

$result = mysqli_query($conn, "SELECT COALESCE(SUM(total_price), 0) AS total FROM reservations
                               WHERE status IN ('confirmed','completed') AND DATE_FORMAT(check_in, '%Y-%m') = '$month'");
$row = mysqli_fetch_assoc($result);
$revenue = $row['total'];

$search = isset($_GET['q']) ? trim($_GET['q']) : "";
$status = isset($_GET['status']) ? $_GET['status'] : "";
$q = mysqli_real_escape_string($conn, $search);

$sql = "SELECT x.*, u.name AS guest_name, u.email AS guest_email, r.room_no, r.room_type,
               DATEDIFF(x.check_out, x.check_in) AS nights
        FROM reservations x
        JOIN users u ON u.id = x.guest_id
        JOIN rooms r ON r.id = x.room_id
        WHERE (u.name LIKE '%$q%' OR u.email LIKE '%$q%' OR r.room_no LIKE '%$q%')";
if (in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
    $sql .= " AND x.status = '$status'";
}
$sql .= " ORDER BY x.status = 'pending' DESC, x.check_in DESC";
$reservations = mysqli_query($conn, $sql);

$title = "Reservations";
$base = "../";
include '../header.php';
?>
<div class="page-head">
  <div>
    <h1>Reservations</h1>
    <p>Confirm new bookings and keep track of who is arriving.</p>
  </div>
  <a href="reservation_form.php" class="btn btn-primary">Add reservation</a>
</div>

<?php if ($message != "") { ?><div class="alert alert-success py-2"><?= h($message) ?></div><?php } ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3"><div class="card stat <?php if ($pending > 0) echo 'alert-stat'; ?>"><div class="label">Waiting for confirmation</div><div class="value"><?= $pending ?></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat"><div class="label">Arriving today</div><div class="value"><?= $arriving ?></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat"><div class="label">Staying now</div><div class="value"><?= $staying ?></div></div></div>
  <div class="col-6 col-md-3"><div class="card stat"><div class="label">Bookings in <?= date('F') ?></div><div class="value">&#8369;<?= number_format($revenue, 2) ?></div></div></div>
</div>

<form class="row g-2 mb-3" method="GET">
  <div class="col-12 col-md">
    <input class="form-control" type="search" name="q" placeholder="Search guest name, email, or room" value="<?= h($search) ?>">
  </div>
  <div class="col-8 col-md-3">
    <select name="status" class="form-select">
      <option value="">All statuses</option>
      <option value="pending" <?php if ($status == 'pending') echo 'selected'; ?>>Pending</option>
      <option value="confirmed" <?php if ($status == 'confirmed') echo 'selected'; ?>>Confirmed</option>
      <option value="completed" <?php if ($status == 'completed') echo 'selected'; ?>>Completed</option>
      <option value="cancelled" <?php if ($status == 'cancelled') echo 'selected'; ?>>Cancelled</option>
    </select>
  </div>
  <div class="col-4 col-md-auto d-flex gap-2">
    <button type="submit" class="btn btn-primary flex-fill">Search</button>
    <?php if ($search != "" || $status != "") { ?><a href="reservations.php" class="btn btn-outline-secondary">Clear</a><?php } ?>
  </div>
</form>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Guest</th><th>Room</th><th>Stay</th><th>Total</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php while ($row = mysqli_fetch_assoc($reservations)) { ?>
        <tr>
          <td>
            <div class="person">
              <span class="avatar"><?= h(strtoupper(substr($row['guest_name'], 0, 1))) ?></span>
              <div><?= h($row['guest_name']) ?><br><small><?= h($row['guest_email']) ?></small></div>
            </div>
          </td>
          <td><strong><?= h($row['room_no']) ?></strong><br><small class="text-muted"><?= h($row['room_type']) ?></small></td>
          <td>
            <?= date('M j, Y', strtotime($row['check_in'])) ?> to <?= date('M j, Y', strtotime($row['check_out'])) ?><br>
            <small class="text-muted"><?= $row['nights'] ?> night(s), <?= $row['guests'] ?> guest(s)</small>
          </td>
          <td>&#8369;<?= number_format($row['total_price'], 2) ?></td>
          <td><span class="badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td class="text-end text-nowrap">
            <form method="POST" class="d-inline">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <?php if ($row['status'] == 'pending') { ?>
                <button type="submit" name="action" value="confirm" class="btn btn-sm btn-success">Confirm</button>
              <?php } elseif ($row['status'] == 'confirmed') { ?>
                <button type="submit" name="action" value="complete" class="btn btn-sm btn-outline-secondary">Check out</button>
              <?php } ?>
            </form>
            <a class="btn btn-sm btn-outline-primary" href="reservation_form.php?id=<?= $row['id'] ?>">Edit</a>
            <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <?php if ($row['status'] == 'pending' || $row['status'] == 'confirmed') { ?>
                <button type="submit" name="action" value="cancel" class="btn btn-sm btn-outline-danger">Cancel</button>
              <?php } else { ?>
                <button type="submit" name="action" value="delete" class="btn btn-sm btn-outline-danger">Delete</button>
              <?php } ?>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
  <?php if (mysqli_num_rows($reservations) == 0) { ?>
    <div class="p-4 text-center text-muted">No reservations found.</div>
  <?php } ?>
</div>
<?php include '../footer.php'; ?>
