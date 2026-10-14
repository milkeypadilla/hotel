<?php

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?> - Hotel Reservation</title>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<div class="auth-wrap">
  <aside class="auth-side">
    <div class="brand"><span class="brand-mark">HR</span> Hotel Reservation</div>
    <div>
      <div class="plate"><small>Room</small><strong>101</strong></div>
      <h2>Pick your dates. Get your room.</h2>
      <p>Guests find a free room and book it in a minute. The hotel confirms and manages every stay.</p>
    </div>
    <div class="small">Hotel Reservation System</div>
  </aside>
  <main class="auth-main">
    <div class="auth-card">
      <h1><?= h($heading) ?></h1>
      <p class="sub"><?= h($sub) ?></p>
