<?php
// Start session to pass error message
session_start();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $exam_id = $_POST['exam_id'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "examps2_db"); // Update with your DB details
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if exam ID exists
    $sql = "SELECT * FROM exams WHERE exam_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Exam ID does not exist, redirect with error
        $_SESSION['error'] = "Exam ID is invalid or does not exist. Please try again.";
        header("Location: enter-exam-id.php");
        exit();
    } else {
        // Exam ID is valid, redirect to take_exam.php
        header("Location: take_exam.php?name=" . urlencode($name) . "&exam_id=" . urlencode($exam_id));
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Exam ID</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5dc; /* Dirty white background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
            position: relative;
        }

        .container {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            border: 3px solid yellowgreen; /* Matching theme border */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 1.5rem;
            font-size: 2rem;
            color: yellowgreen; /* Title color updated */
        }

        .error-message {
            color: #dc3545; /* Red for error */
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-align: left;
        }

        input[type="text"] {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1.5rem;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            padding: 0.8rem 2rem;
            background-color: yellowgreen; /* Button color updated */
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #7fbf3f; /* Hover effect */
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 0.6rem 1.5rem;
            background-color: yellowgreen; /* Back button color */
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #7fbf3f;
        }
    </style>
</head>
<body background= "C:\Users\63907\Downloads\school logo.jpg">
    <!-- Back Button Positioned in Upper Left -->
    <a href="homepage.html" class="back-btn">Back to Homepage</a>

    <div class="container">
        <h1>Enter Exam ID</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='error-message'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']); // Clear the error message
        }
        ?>

        <form method="GET" action="take_exam.php">
            <input type="text" name="name" placeholder="Enter your full name" required>
            <input type="text" name="exam_id" placeholder="Enter exam ID" required>
            <button type="submit">Start Exam</button>
        </form>

    </div>
</body>
</html>
