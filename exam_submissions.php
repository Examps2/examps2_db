<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $name = $_POST['student_name']; // or get from session if already saved
    $exam_id = intval($_POST['exam_id']);
    $answers = $_POST['answers']; // this should be an associative array: question_id => selected_choice_id

    $score = 0;
    $total_correct = 0;
    $submission_time = date("Y-m-d H:i:s");

    // Loop through answers
    foreach ($answers as $question_id => $selected_choice_id) {
        // Check if the selected choice is correct
        $stmt = $conn->prepare("SELECT is_correct FROM choices WHERE id = ?");
        $stmt->bind_param("i", $selected_choice_id);
        $stmt->execute();
        $stmt->bind_result($is_correct);
        $stmt->fetch();
        $stmt->close();

        if ($is_correct == 1) {
            $score++;
            $total_correct++;
        }
    }

    // Insert into exam_submissions table
    $insert_stmt = $conn->prepare("INSERT INTO exam_submissions (exam_id, name, submission_time, score, correct_answers_total)
                                   VALUES (?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("issii", $exam_id, $name, $submission_time, $score, $total_correct);
    
    if ($insert_stmt->execute()) {
        echo "<h2>Exam submitted successfully!</h2>";
        echo "<p><strong>Name:</strong> $name</p>";
        echo "<p><strong>Score:</strong> $score</p>";
        echo "<p><strong>Correct Answers:</strong> $total_correct</p>";
        echo "<a href='exam_results.php?exam_id=$exam_id'>View Results</a>";
    } else {
        echo "Error: " . $insert_stmt->error;
    }

    $insert_stmt->close();
    $conn->close();
} else {
    echo "Invalid submission method.";
}
?>
