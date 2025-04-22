<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// --- Candidate Addition ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo']) && isset($_POST['name']) && isset($_POST['designation'])) {
    $name = $conn->real_escape_string(trim($_POST['name']));
    $designation = $conn->real_escape_string(trim($_POST['designation']));
    
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    $uniquePrefix = uniqid();
    $target_file = $target_dir . $uniquePrefix . "_" . basename($_FILES["photo"]["name"]);
    
    if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO candidates (name, designation, photo_path) VALUES (?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sss", $name, $designation, $target_file);
            $stmt->execute();
            $stmt->close();
        } else {
            $_SESSION['error'] = "Prepared statement error: " . $conn->error;
        }
    } else {
        $_SESSION['error'] = "Error uploading file.";
    }
    header("Location: admin_panel.php");
    exit();
}

// --- Candidate Deletion ---
if (isset($_GET['delete_candidate'])) {
    $candidate_id = intval($_GET['delete_candidate']);
    // Optionally, delete the file if it exists:
    $stmt = $conn->prepare("SELECT photo_path FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $stmt->bind_result($photo_path);
    if ($stmt->fetch() && $photo_path && file_exists($photo_path)) {
        unlink($photo_path);
    }
    $stmt->close();
    
    $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_panel.php");
    exit();
}

// --- Voter Deletion ---
if (isset($_GET['delete_voter'])) {
    $voter_id = intval($_GET['delete_voter']);
    // Delete only if role = 'user'
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'user'");
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin_panel.php");
    exit();
}

// Retrieve candidates and voters for display:
$candidatesResult = $conn->query("SELECT * FROM candidates");
$votersResult = $conn->query("SELECT * FROM users WHERE role = 'user'");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="admin_style.css">
    <style>

        .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 2px solid #ddd;
        padding-bottom: 10px;
        margin-bottom: 20px;
        position: relative;
        }

        .header-title-group {
        text-align: center;
        flex-grow: 1;
         }

        .header-title {
        font-size: 24px;
        font-weight: bold;
        }

        .header-heading {
        font-size: 20px;
        color: #333;          /* Darker gray */
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1); /* Subtle depth */
        margin-top: 8px;
        }

        .fancy-logout {
        background: linear-gradient(45deg, #ff4b1f, #ff9068);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s, box-shadow 0.3s;
        /* Position at far right */
        margin-left: auto;
        }

        .fancy-logout:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 10px rgba(0,0,0,0.15);
        text-decoration: none;
        }

        .columns-container {
            display: flex;
            gap: 20px;
            margin-top: 30px;
        }
        .left-column, .right-column {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .candidate-card-small {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            padding: 8px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .candidate-card-small img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 10px;
        }
        .voter-list li {
            margin-bottom: 8px;
            list-style: none;
        }
    </style>
</head>
<body>
<div class="admin-container">
    <!-- Modified Header Section -->
    <div class="header-container">
        <div class="header-title-group">
            <div class="header-title">Admin Dashboard</div>
            <div class="header-heading">Add New Candidate</div>
        </div>
        <a href="logout.php" class="fancy-logout">Logout</a>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="name" placeholder="Candidate Name" required>
        <input type="text" name="designation" placeholder="Designation" required>
        <input type="file" name="photo" accept="image/*" required>
        <button type="submit">Add Candidate</button>
    </form>

    <div class="columns-container">
        <div class="left-column">
            <h3>Candidates List</h3>
            <?php while ($row = $candidatesResult->fetch_assoc()): ?>
                <div class="candidate-card-small">
                    <img src="<?= htmlspecialchars($row['photo_path']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <div>
                        <strong><?= htmlspecialchars($row['name']) ?></strong><br>
                        <small><?= htmlspecialchars($row['designation']) ?></small>
                    </div>
                    <div style="margin-left:auto;">
                        <a href="admin_panel.php?delete_candidate=<?= $row['id'] ?>" class="delete-btn" onclick="return confirm('Delete this candidate?');">Delete</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="right-column">
            <h3>Registered Voters</h3>
            <ul class="voter-list">
                <?php while ($voter = $votersResult->fetch_assoc()): ?>
                    <li>
                        <?= htmlspecialchars($voter['username']) ?>
                        <a href="admin_panel.php?delete_voter=<?= $voter['id'] ?>" class="delete-btn" onclick="return confirm('Delete this voter?');">[Delete]</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
    
    <h3 style="margin-top: 20px;">View Results</h3>
    <div style="text-align: center; margin-top: 20px;">
        <a href="results.php" class="btn" style="cursor: pointer;">View Voting Results</a>
    </div>
</div>
</body>
</html>