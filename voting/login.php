<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $input_password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = 'user'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($input_password === $user['password']) {
            // Check if the user has already voted
            if ($user['voted']) {
                echo "<script>
                    alert('You have already cast your vote and cant login. Thank you for your vote.');
                    window.location.href='index.php';
                </script>";
                exit();
            }
            $_SESSION['user_id'] = $user['id'];
            header("Location: vote.php");
            exit();
        }
    }
    $_SESSION['error'] = "Invalid username or password!";
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Voter Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Voter Login</h2>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="registration.php">Register here</a></p>
</div>
</body>
</html>
