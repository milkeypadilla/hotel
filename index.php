<?php

include 'connection.php';

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
} elseif ($_SESSION['role'] == 'admin') {
    header("Location: admin/reservations.php");
} else {
    header("Location: guest/search.php");
}
exit;
?>
