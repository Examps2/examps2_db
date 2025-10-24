<?php
include('db.php'); // Make sure this connects to your database

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get values from form
    $exam_id = $_POST['exam_id'];
    $question = $_POST['question_text'];

    // Sanitize input (recommended)
    $exam_id = mysqli_real_escape_string($conn, $exam_id);
    $question = mysqli_real_escape_string($conn, $question);

    // Check if exam exists
    $check = mysqli_query($conn, "SELECT id FROM exams WHERE id = '$exam_id'");
    
    if (mysqli_num_rows($check) > 0) {
        // Insert the question
        $insert = mysqli_query($conn, "INSERT INTO questions (exam_id, question_text) VALUES ('$exam_id', '$question')");
        
        if ($insert) {
            echo "✅ Question inserted successfully!";
        } else {
            echo "❌ Insert failed: " . mysqli_error($conn);
        }
    } else {
        echo "⚠️ Exam ID not found in exams table!";
    }
} else {
    echo "Invalid request method. Please submit the form properly.";
}
?>
