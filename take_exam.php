<?php
include 'db.php';

$exam_id = 0;
$exam_name = '';
$questions_data = [];

// Check if exam ID is provided via GET
if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id']; // ✅ do NOT use intval
    $stmt = $conn->prepare("SELECT exam_name FROM exams WHERE exam_id = ?");
    $stmt->bind_param("s", $exam_id); // ✅ bind as string
    $stmt->execute();
    $stmt->bind_result($exam_name);
    $stmt->fetch();
    $stmt->close();

    if (empty($exam_name)) {
        // Redirect to homepage with error if exam ID is invalid
        header("Location: homepage.html?error=invalid");
        exit();
    }

    // ✅ FIX: Add quotes around exam_id for string type
    $questions_result = $conn->query("SELECT * FROM questions WHERE exam_id = '$exam_id'");
    if (!$questions_result) {
        die("Error fetching questions: " . $conn->error);
    }

    while ($question = $questions_result->fetch_assoc()) {
        $question_id = $question['id'];
        $choices_result = $conn->query("SELECT * FROM choices WHERE question_id = $question_id");
        $choices_data = $choices_result->fetch_all(MYSQLI_ASSOC);
        $questions_data[] = [
            'id' => $question_id,
            'question_text' => $question['question_text'],
            'choices' => $choices_data
        ];
    }
} else {
    echo "No exam ID provided.";
    exit;
}

$name = isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Exam</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5; /* Dirty White */
            color: #333;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        h1 {
            margin-bottom: 20px;
            color: #9ACD32; /* Yellow-Green */
        }

        .question-container {
            margin-bottom: 20px;
            padding: 15px;
            background: #fafafa;
            border-radius: 8px;
            border: 1px solid #ddd;
        }

        .choice-container {
            margin: 10px 0;
        }

        .choice-label {
            font-weight: bold;
        }

        button {
            background-color: #9ACD32; /* Yellow-Green */
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        button:hover {
            opacity: 0.85;
        }

        /* dark mode */
        body.dark-mode {
            background-color: #121212;
            color: #e0e0e0;
        }

        .container.dark-mode {
            background-color: #1e1e1e;
        }

        .question-container.dark-mode {
            background-color: #2a2a2a;
            border: 1px solid #444;
        }

        .dark-mode-toggle {
            background-color: #9ACD32; /* Yellow-Green */
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            position: fixed;
            top: 10px;
            right: 20px;
        }

        .dark-mode-toggle:hover {
            opacity: 0.85;
        }
    </style>
</head>
<body>
    <button class="dark-mode-toggle" onclick="toggleDarkMode()">Enable Dark Mode</button>

    <div class="container">
        <h1>Exam: <?php echo htmlspecialchars($exam_name); ?></h1>
        
        <form method="post" action="submit_exam.php">
            <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">

            <?php if (!empty($_GET['name'])): ?>
                <input type="hidden" name="name" value="<?php echo htmlspecialchars($_GET['name']); ?>">
            <?php else: ?>
                <label for="name">Your Name:</label>
                <input type="text" id="name" name="name" required>
            <?php endif; ?>

            <?php if (empty($questions_data)): ?>
                <p style="color:red;">No questions found for this exam.</p>
            <?php endif; ?>

            <?php foreach ($questions_data as $question): ?>
                <div class="question-container">
                    <p><?php echo 'Question: ' . htmlspecialchars($question['question_text']); ?></p>
                    <?php foreach ($question['choices'] as $key => $choice): ?>
                        <div class="choice-container">
                            <input type="radio" 
                                   id="choice_<?php echo $question['id']; ?>_<?php echo $key; ?>" 
                                   name="questions[<?php echo $question['id']; ?>][choice]" 
                                   value="<?php echo $choice['id']; ?>" 
                                   required>
                            <label for="choice_<?php echo $question['id']; ?>_<?php echo $key; ?>" class="choice-label">
                                <?php echo chr(65 + $key) . ': ' . htmlspecialchars($choice['choice_text']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit">Submit Exam</button>
        </form>
    </div>

    <script>
        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode');
            document.querySelectorAll('.container, .question-container').forEach((element) => {
                element.classList.toggle('dark-mode');
            });

            const darkModeButton = document.querySelector('.dark-mode-toggle');
            if (document.body.classList.contains('dark-mode')) {
                darkModeButton.textContent = 'Disable Dark Mode';
            } else {
                darkModeButton.textContent = 'Enable Dark Mode';
            }
        }
    </script>
</body>
</html>
