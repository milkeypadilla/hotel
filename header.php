<?php

$page = basename($_SERVER['PHP_SELF']);
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?> - Hotel Reservation</title>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= $base ?>assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-md topbar py-2">
  <div class="container">
    <a class="navbar-brand" href="<?= $base ?>index.php"><span class="brand-mark">HR</span> Hotel Reservation</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav me-auto ms-md-3 gap-md-1">
        <?php if ($role == 'admin') { ?>
          <li class="nav-item"><a class="nav-link <?php if ($page == 'reservations.php' || $page == 'reservation_form.php') echo 'active'; ?>" href="<?= $base ?>admin/reservations.php">Reservations</a></li>
          <li class="nav-item"><a class="nav-link <?php if ($page == 'rooms.php') echo 'active'; ?>" href="<?= $base ?>admin/rooms.php">Rooms</a></li>
          <li class="nav-item"><a class="nav-link <?php if ($page == 'guests.php') echo 'active'; ?>" href="<?= $base ?>admin/guests.php">Guests</a></li>
        <?php } elseif ($role == 'guest') { ?>
          <li class="nav-item"><a class="nav-link <?php if ($page == 'search.php' || $page == 'book.php') echo 'active'; ?>" href="<?= $base ?>guest/search.php">Find a room</a></li>
          <li class="nav-item"><a class="nav-link <?php if ($page == 'reservations.php') echo 'active'; ?>" href="<?= $base ?>guest/reservations.php">My reservations</a></li>
        <?php } ?>
      </ul>
      <ul class="navbar-nav align-items-md-center gap-md-2">
        <li class="nav-item"><span class="nav-user"><?= h($_SESSION['name']) ?></span></li>
        <li class="nav-item"><a class="btn btn-outline-primary btn-sm" href="<?= $base ?>logout.php">Log out</a></li>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4">
