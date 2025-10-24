<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Access denied.");
}

include 'db.php';

// Admin panel to approve users
$pending_users_query = "SELECT id, username FROM users WHERE approved = 0";
$pending_result = $conn->query($pending_users_query);

// Query to fetch all registered users
$all_users_query = "SELECT id, username, approved FROM users";
$all_users_result = $conn->query($all_users_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5dc; /* Dirty white like homepage */
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            border: 3px solid yellowgreen; /* Theme */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1, h2 {
            margin-bottom: 20px;
            color: yellowgreen;
        }

        .user-item {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .approve-button, .logout-button {
            background-color: yellowgreen;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
            margin-right: 10px;
            font-weight: bold;
        }

        .approve-button:hover, .logout-button:hover {
            background-color: #7fbf3f; /* Darker green */
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .user-table th, .user-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        .user-table th {
            background-color: #9acd32; /* Yellowgreen header */
            color: #fff;
        }

        .user-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .user-table tr:hover {
            background-color: #eef5e3;
        }

        @media (max-width: 768px) {
            .user-table, .user-table thead, .user-table tbody, .user-table th, .user-table td, .user-table tr {
                display: block;
            }

            .user-table th {
                display: none;
            }

            .user-table td {
                display: flex;
                justify-content: space-between;
                padding: 10px 0;
                border-bottom: 1px solid #ddd;
                position: relative;
            }

            .user-table td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 50%;
                padding-right: 10px;
                font-weight: bold;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Panel</h1>
        
        <a href="logout.php" class="logout-button">Logout</a>
        
        <h2>Approve Users</h2>
        <?php
        if ($pending_result->num_rows > 0) {
            while ($row = $pending_result->fetch_assoc()) {
                echo "<div class='user-item'>
                    {$row['username']} 
                    <a href='approve_user.php?id={$row['id']}' class='approve-button'>Approve</a>
                </div>";
            }
        } else {
            echo "<p>No pending users to approve.</p>";
        }
        ?>

        <h2>All Registered Users</h2>
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Approved</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($all_users_result->num_rows > 0) {
                    while ($row = $all_users_result->fetch_assoc()) {
                        echo "<tr>
                            <td data-label='ID'>{$row['id']}</td>
                            <td data-label='Username'>{$row['username']}</td>
                            <td data-label='Approved'>" . ($row['approved'] ? 'Yes' : 'No') . "</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No users found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
$conn->close();
?>
