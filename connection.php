<?php

session_start();
date_default_timezone_set('Asia/Manila');

$servername = "localhost";
$username = "root";
$password = "";
$database = "hotel_reservationn";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

function h($text) {
    return htmlspecialchars($text);
}
?>
