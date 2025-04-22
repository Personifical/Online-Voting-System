<?php
session_start();
include 'db.php';

// Only allow access if admin is logged in; otherwise, redirect to admin_panel.php.
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_panel.php");
    exit();
}

$result = $conn->query("SELECT * FROM candidates ORDER BY votes DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voting Results</title>
    <link rel="stylesheet" href="admin_style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background: #f2f2f2;
        }
        .candidate-img {
            max-width: 80px;
            max-height: 80px;
            object-fit: cover;
        }
        /* Back button at bottom right */
        .back-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #0d6efd;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        .back-btn:hover {
            background: #0a58ca;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <h2>Voting Results</h2>
    <table>
        <tr>
            <th>Candidate Photo</th>
            <th>Candidate Name</th>
            <th>Designation</th>
            <th>Votes</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><img class="candidate-img" src="<?= htmlspecialchars($row['photo_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>"></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['designation']) ?></td>
            <td><?= $row['votes'] ?></td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
<a class="back-btn" href="admin_panel.php">Back</a>
</body>
</html>
