<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db.php';

// Check if exam_id is provided
if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];

    // Get submissions + exam info
    $sql = "SELECT exam_submissions.id AS submission_id, exam_submissions.name, exam_submissions.submission_time, exams.exam_name, exams.max_score, exam_submissions.score 
            FROM exam_submissions 
            JOIN exams ON exam_submissions.exam_id = exams.exam_id
            WHERE exams.exam_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "Prepare failed: (" . $conn->errno . ") " . $conn->error;
        exit();
    }

    $stmt->bind_param("i", $exam_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $totalScore = 0;
    $studentCount = 0;
    $submissions = [];
    $maxScore = 0;
    $examName = "";

    while ($row = $result->fetch_assoc()) {
        $totalScore += $row['score'];
        $studentCount++;
        $maxScore = $row['max_score']; // highest possible score
        $examName = $row['exam_name'];
        $submissions[] = $row;
    }

    // Calculate overall MPS
    $mps = 0;
    if ($studentCount && $maxScore > 0) {
        $averageScore = $totalScore / $studentCount;
        $mps = ($averageScore / $maxScore) * 100;
    }

} else {
    echo "Invalid exam ID.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Exam Results with MPS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F5F5DC; /* dirty white */
            color: #333;
        }
        h2, h3 {
            text-align: center;
            color: #556B2F;
        }
        .mps-box {
            background-color: #9ACD32; /* yellowgreen */
            color: white;
            text-align: center;
            padding: 12px;
            margin: 20px auto;
            width: 350px;
            border-radius: 8px;
            font-weight: bold;
        }
        .table-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            background-color: #F5F5DC;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            border: 1px solid #9ACD32;
            padding: 12px 15px;
            text-align: center;
        }
        th {
            background-color: #9ACD32;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f0f0d8;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #9ACD32;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #7ea523;
        }
    </style>
</head>
<body>
    <h2>Exam Results</h2>

    <div class="mps-box">
        Exam: <?php echo htmlspecialchars($examName); ?><br>
        Real-Time Overall MPS: <?php echo number_format($mps, 2); ?>%
    </div>

    <div class="table-container">
        <table>
            <tr>
                <th>Student Name</th>
                <th>Exam Name</th>
                <th>Submission Time</th>
                <th>Score</th>
                <th>Individual MPS (%)</th>
                <th>View</th>
            </tr>

            <?php if (!empty($submissions)): ?>
                <?php foreach ($submissions as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['exam_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['submission_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['score']); ?></td>
                        <td>
                            <?php 
                                if ($maxScore > 0) {
                                    echo number_format(($row['score'] / $maxScore) * 100, 2);
                                } else {
                                    echo "N/A";
                                }
                            ?>%
                        </td>
                        <td>
                            <a href="view_score.php?submission_id=<?php echo $row['submission_id']; ?>" class="btn">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No submissions found for this exam.</td></tr>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>