<?php
include '../connection.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

$message = "";
$error = "";

$id = 0;
$room_no = "";
$room_type = "";
$capacity = 2;
$price = "";
$description = "";

if (isset($_POST['delete'])) {
    $delete_id = (int)$_POST['id'];
    $result = mysqli_query($conn, "SELECT id FROM reservations WHERE room_id = $delete_id");
    if (mysqli_num_rows($result) > 0) {
        $error = "This room has reservations, so it cannot be deleted.";
    } else {
        mysqli_query($conn, "DELETE FROM rooms WHERE id = $delete_id");
        $message = "Room deleted.";
    }
}

if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM rooms WHERE id = $edit_id");
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $id = $row['id'];
        $room_no = $row['room_no'];
        $room_type = $row['room_type'];
        $capacity = $row['capacity'];
        $price = $row['price_per_night'];
        $description = $row['description'];
    }
}

if (isset($_POST['save'])) {
    $id = (int)$_POST['id'];
    $room_no = trim($_POST['room_no']);
    $room_type = trim($_POST['room_type']);
    $capacity = (int)$_POST['capacity'];
    $price = $_POST['price'];
    $description = trim($_POST['description']);

    $room_no_safe = mysqli_real_escape_string($conn, $room_no);
    $room_type_safe = mysqli_real_escape_string($conn, $room_type);
    $description_safe = mysqli_real_escape_string($conn, $description);

    if ($room_no == "" || $room_type == "") {
        $error = "Room number and type are required.";
    } elseif ($capacity < 1) {
        $error = "The room must fit at least 1 guest.";
    } elseif (!is_numeric($price) || $price < 0) {
        $error = "Enter a valid price per night.";
    } else {
        $result = mysqli_query($conn, "SELECT id FROM rooms WHERE room_no = '$room_no_safe' AND id <> $id");
        if (mysqli_num_rows($result) > 0) {
            $error = "That room number already exists.";
        } elseif ($id > 0) {
            $sql = "UPDATE rooms SET room_no='$room_no_safe', room_type='$room_type_safe', capacity=$capacity,
                    price_per_night=$price, description='$description_safe' WHERE id=$id";
            mysqli_query($conn, $sql);
            $message = "Room updated.";
        } else {
            $sql = "INSERT INTO rooms (room_no, room_type, capacity, price_per_night, description)
                    VALUES ('$room_no_safe', '$room_type_safe', $capacity, $price, '$description_safe')";
            mysqli_query($conn, $sql);
            $message = "Room added.";
        }
        if ($error == "") {
            $id = 0; $room_no = ""; $room_type = ""; $capacity = 2; $price = ""; $description = "";
        }
    }
}

$sql = "SELECT r.*,
        (SELECT COUNT(*) FROM reservations x
         WHERE x.room_id = r.id AND x.status IN ('pending','confirmed') AND x.check_out > CURDATE()) AS upcoming
        FROM rooms r ORDER BY r.room_no";
$rooms = mysqli_query($conn, $sql);

$title = "Rooms";
$base = "../";
include '../header.php';
?>
<div class="page-head">
  <div>
    <h1>Rooms</h1>
    <p>Set the type, size and nightly price of each room.</p>
  </div>
</div>

<?php if ($message != "") { ?><div class="alert alert-success py-2"><?= h($message) ?></div><?php } ?>
<?php if ($error != "") { ?><div class="alert alert-danger py-2"><?= h($error) ?></div><?php } ?>

<div class="card mb-4">
  <div class="card-header"><?php echo ($id > 0) ? "Edit room" : "Add a room"; ?></div>
  <div class="card-body">
    <form method="POST" class="row g-3">
      <input type="hidden" name="id" value="<?= $id ?>">
      <div class="col-6 col-md-2">
        <label class="form-label">Room no.</label>
        <input type="text" name="room_no" class="form-control" placeholder="101" value="<?= h($room_no) ?>" required>
      </div>
      <div class="col-6 col-md-3">
        <label class="form-label">Type</label>
        <input type="text" name="room_type" class="form-control" placeholder="Deluxe Double" value="<?= h($room_type) ?>" required>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Sleeps</label>
        <input type="number" name="capacity" class="form-control" min="1" value="<?= h($capacity) ?>" required>
      </div>
      <div class="col-6 col-md-2">
        <label class="form-label">Price / night</label>
        <input type="number" name="price" class="form-control" min="0" step="0.01" value="<?= h($price) ?>" required>
      </div>
      <div class="col-12 col-md-3">
        <label class="form-label">Description</label>
        <input type="text" name="description" class="form-control" value="<?= h($description) ?>">
      </div>
      <div class="col-12 d-flex gap-2">
        <button type="submit" name="save" class="btn btn-primary">Save room</button>
        <?php if ($id > 0) { ?><a href="rooms.php" class="btn btn-outline-secondary">Cancel</a><?php } ?>
      </div>
    </form>
  </div>
</div>

<div class="table-card">
  <div class="table-responsive">
    <table class="table align-middle">
      <thead><tr><th>Room</th><th>Type</th><th>Sleeps</th><th>Price / night</th><th>Upcoming stays</th><th></th></tr></thead>
      <tbody>
      <?php while ($row = mysqli_fetch_assoc($rooms)) { ?>
        <tr>
          <td><strong><?= h($row['room_no']) ?></strong></td>
          <td><?= h($row['room_type']) ?><br><small class="text-muted"><?= h($row['description']) ?></small></td>
          <td><?= $row['capacity'] ?></td>
          <td>&#8369;<?= number_format($row['price_per_night'], 2) ?></td>
          <td><?= $row['upcoming'] ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-sm btn-outline-primary" href="rooms.php?edit=<?= $row['id'] ?>">Edit</a>
            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this room?')">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">Delete</button>
            </form>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>
  <?php if (mysqli_num_rows($rooms) == 0) { ?>
    <div class="p-4 text-center text-muted">No rooms yet. Add the first room above.</div>
  <?php } ?>
</div>
<?php include '../footer.php'; ?>
