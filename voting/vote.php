<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $conn->query("SELECT voted FROM users WHERE id = $user_id")->fetch_assoc();

if ($user['voted']) {
    header("Location: results.php");
    exit();
}

$candidates = $conn->query("SELECT * FROM candidates");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Voting Booth</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .candidate {
            margin: 10px 0;
            padding: 5px;
            border-bottom: 1px solid #ccc;
            display: flex;
            align-items: center;
        }
        .candidate img {
            max-width: 80px;
            margin-right: 15px;
        }
        .candidate label {
            flex: 1;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Select Your Candidate</h2>
    <form action="vote_process.php" method="POST">
        <?php while ($row = $candidates->fetch_assoc()): ?>
            <div class="candidate">
                <?php if ($row['photo_path']): ?>
                    <img src="<?= htmlspecialchars($row['photo_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                <?php endif; ?>
                <label>
                    <input type="radio" name="candidate_id" value="<?= $row['id'] ?>" required>
                    <?= htmlspecialchars($row['name']) ?> <br> <small><?= htmlspecialchars($row['designation']) ?></small>
                </label>
            </div>
        <?php endwhile; ?>
        <button type="submit">Submit Vote</button>
    </form>
</div>
</body>
</html>
