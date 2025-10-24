<?php
// Replace these with your InfinityFree MySQL details
$servername = "sql210.infinityfree.com";  // Example: sql308.infinityfree.com
$username = "if0_40022684";              // Example: epiz_12345678
$password = "OiTxDl3wqbVb7";              // Your InfinityFree MySQL password
$dbname = "if0_40022684_examps2";     // Example: epiz_12345678_examps2_db

// Try connecting to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
} else {
    echo "✅ Connected successfully to the database!";
}
?>