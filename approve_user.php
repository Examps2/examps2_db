<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    die("Access denied.");
}

include 'db.php';

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Update user status to approved
    $stmt = $conn->prepare("UPDATE users SET approved = 1 WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo "User approved successfully. <a href='admin_panel.php'>Back to Admin Panel</a>";
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
    $stmt->close();
} else {
    echo "No user ID specified.";
}

$conn->close();
?>