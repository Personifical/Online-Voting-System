<?php
$servername = "localhost";
$port = 3307; // MySQL port
$username = "root";
$password = "";  
$dbname = "voting";

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
