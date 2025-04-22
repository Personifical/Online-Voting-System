<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Online Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Online Voting System</h1>
    <div class="options">
        <a href="login.php" class="btn voter-btn">Voter Login</a>
        <a href="admin_login.php" class="btn admin-btn">Admin Login</a>
    </div>
    <div class="registration-link" style="margin-top: 20px;">
        <p>New Voter? <a href="registration.php">Register here</a></p>
    </div>
</div>
</body>
</html>
