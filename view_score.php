<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

if (!isset($_GET['submission_id'])) {
    echo "No submission ID provided.";
    exit();
}

$submission_id = isset($_GET['submission_id']) ? $_GET['submission_id'] : '';

// Fetch submission details
$sql = "SELECT exam_submissions.name, exams.exam_name, exam_submissions.score, exam_submissions.submission_time 
        FROM exam_submissions 
        JOIN exams ON exam_submissions.exam_id = exams.exam_id 
        WHERE exam_submissions.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Submission not found.";
    exit();
}

$data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Score</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F5F5DC; /* dirty white */
            color: #333;
        }
        .container {
            width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #556B2F;
        }
        .info {
            margin-top: 20px;
        }
        .info p {
            margin: 10px 0;
            font-size: 16px;
        }
        .label {
            font-weight: bold;
            color: #9ACD32;
        }
        .btn-back {
            display: block;
            width: fit-content;
            margin: 20px auto 0;
            padding: 10px 20px;
            background-color: #9ACD32;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        .btn-back:hover {
            background-color: #7ea523;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Student Score</h2>
        <div class="info">
            <p><span class="label">Student Name:</span> <?php echo htmlspecialchars($data['name']); ?></p>
            <p><span class="label">Exam Name:</span> <?php echo htmlspecialchars($data['exam_name']); ?></p>
            <p><span class="label">Score:</span> <?php echo htmlspecialchars($data['score']); ?></p>
            <p><span class="label">Submitted On:</span> <?php echo htmlspecialchars($data['submission_time']); ?></p>
        </div>
        <a href="homepage.html" class="btn-back">Go Back</a>
    </div>
</body>
</html>
 