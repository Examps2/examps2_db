<?php
// Your InfinityFree MySQL settings — change these to match your account details
$servername = "sql210.infinityfree.com"; // replace XXX with your actual MySQL hostname
$username = "if0_40022684"; // your InfinityFree MySQL username
$password = "OiTxDl3wqbVb7"; // your InfinityFree MySQL password
$dbname = "if0_40022684_examps2_db"; // your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
