<?php
ob_start(); // Start output buffering to prevent header issues
session_start();
include 'db.php'; // Include your database connection

// Check if database connection exists
if (!isset($conn) || !$conn) {
    die("Database connection failed.");
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure the username and password are set
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        // Prepare the SQL statement
        $stmt = $conn->prepare("SELECT id, password, is_teacher, is_admin, approved FROM users WHERE username = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $user_id = $row['id'];
            $stored_password = $row['password'];
            $is_teacher = $row['is_teacher'];
            $is_admin = $row['is_admin'];
            $approved = $row['approved'];

            // Check if the account is approved
            if (!$approved) {
                $error_message = "Your account is not approved.";
            } elseif ($password === $stored_password) { // Plain text comparison
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['is_teacher'] = $is_teacher;
                $_SESSION['is_admin'] = $is_admin;

                // Redirect properly
                if ($is_admin) {
                    header("Location: admin_panel.php");
                    exit();
                } else {
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $error_message = "Invalid username or password.";
            }
        } else {
            $error_message = "Invalid username or password.";
        }

        $stmt->close();
    } else {
        $error_message = "Please fill out all fields.";
    }
}

$conn->close();
ob_end_flush(); // End buffering
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
       body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #333;
        }

        .container {
            background: #f9f9f6;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            color: yellowgreen;
            font-size: 30px;
            margin-bottom: 20px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input[type="text"], input[type="password"] {
            width: 95%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }

        button {
            background-color: yellowgreen;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }

        button:hover {
            background-color: #9acd32;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
        }

        .no-account {
            margin-top: 20px;
            text-align: center;
        }

        .no-account a {
            color: yellowgreen;
            text-decoration: none;
        }

        .no-account a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php
        if (isset($error_message)) {
            echo "<p class='error-message'>$error_message</p>";
        }
        ?>
        <form method="post" action="login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <div class="no-account">
            <p>Don't have an account? <a href="registration.php">Register here.</a></p>
        </div>
    </div>
</body>
</html>