<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: vote.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$candidate_id = intval($_POST['candidate_id']);

$stmt = $conn->prepare("SELECT voted FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user['voted']) {
    $_SESSION['error'] = "You have already voted!";
    header("Location: vote.php");
    exit();
}

$stmt = $conn->prepare("SELECT id FROM candidates WHERE id = ?");
$stmt->bind_param("i", $candidate_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Invalid candidate selection!";
    header("Location: vote.php");
    exit();
}

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("UPDATE candidates SET votes = votes + 1 WHERE id = ?");
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    
    $stmt = $conn->prepare("UPDATE users SET voted = TRUE WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    
    $conn->commit();
    
    echo "<script>
        alert('Thank you. Your vote has been successfully cast. Please return to the homepage.');
        window.location.href='logout.php';
    </script>";
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error submitting vote: " . $e->getMessage();
    header("Location: vote.php");
    exit();
}
?>
