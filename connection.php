<?php

session_start();
date_default_timezone_set('Asia/Manila');

$servername = "sql208.infinityfree.com";
$username = "if0_43169751";
$password = "0anQ4KCoXFRc6AK";
$database = "if0_43169751_hotel";

$conn = mysqli_connect($servername, $username, $password, $database);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

function h($text) {
    return htmlspecialchars($text);
}
?>
