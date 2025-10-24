<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id'];

include 'db.php';

$exam_name = '';
$exam_id = '';
$success_message = '';

// 6 digit id
function generateUniqueExamId($conn) {
    do {
        $exam_id = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT); // exam id generator
        $stmt = $conn->prepare("SELECT COUNT(*) FROM exams WHERE id = ?");
        $stmt->bind_param("s", $exam_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
    } while ($count > 0);

    return $exam_id;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exam_name = $_POST['exam_name'];
    $exam_id = isset($_POST['exam_id']) ? $_POST['exam_id'] : '';

    if (empty($exam_name)) {
        die('Exam name cannot be empty.');
    }

    $user_id = $_SESSION['user_id'];

    if (empty($exam_id)) {
        $exam_id = generateUniqueExamId($conn);

        $stmt = $conn->prepare("INSERT INTO exams (exam_id, exam_name, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $exam_id, $exam_name, $user_id);
        if (!$stmt->execute()) {
            die('Insert failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();

        $success_message = "Exam saved successfully! Exam ID: " . htmlspecialchars($exam_id);
    } else {
        $stmt = $conn->prepare("UPDATE exams SET exam_name = ? WHERE exam_id = ?");
        $stmt->bind_param("ss", $exam_name, $exam_id);
        if (!$stmt->execute()) {
            die('Update failed: ' . htmlspecialchars($stmt->error));
        }
        $stmt->close();

        $success_message = "Exam updated successfully! Exam ID: " . htmlspecialchars($exam_id);
    }

    // Handle Questions
    $questions = isset($_POST['questions']) ? $_POST['questions'] : [];

    $max_question_sql = "SELECT COALESCE(MAX(question_number), 0) AS max_question_number FROM questions WHERE exam_id = ?";
    $max_question_stmt = $conn->prepare($max_question_sql);
    $max_question_stmt->bind_param("s", $exam_id);
    $max_question_stmt->execute();
    $max_question_stmt->bind_result($max_question_number);
    $max_question_stmt->fetch();
    $max_question_stmt->close();

    $next_question_number = $max_question_number + 1;

    foreach ($questions as $question) {
        $question_id = isset($question['id']) ? intval($question['id']) : 0;
        $question_text = $question['question_text'];

        if ($question_id == 0) {
            $stmt = $conn->prepare("INSERT INTO questions (question_text, exam_id, question_number) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $question_text, $exam_id, $next_question_number);
            if (!$stmt->execute()) {
                die('Insert failed: ' . htmlspecialchars($stmt->error));
            }
            $question_id = $stmt->insert_id;
            $stmt->close();

            $next_question_number++;
        } else {
            $stmt = $conn->prepare("UPDATE questions SET question_text = ? WHERE id = ?");
            $stmt->bind_param("si", $question_text, $question_id);
            if (!$stmt->execute()) {
                die('Update failed: ' . htmlspecialchars($stmt->error));
            }
            $stmt->close();
        }

        $choices = $question['choices'] ?? [];
        foreach ($choices as $key => $choice) {
            $choice_id = isset($choice['id']) ? intval($choice['id']) : 0;
            $choice_text = $choice['choice_text'];
            $is_correct = isset($choice['is_correct']) ? 1 : 0;

            if ($choice_id == 0) {
                $stmt = $conn->prepare("INSERT INTO choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $question_id, $choice_text, $is_correct);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmt = $conn->prepare("UPDATE choices SET choice_text = ?, is_correct = ? WHERE id = ?");
                $stmt->bind_param("sii", $choice_text, $is_correct, $choice_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}

if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];
    $stmt = $conn->prepare("SELECT exam_name FROM exams WHERE exam_id = ?");
    $stmt->bind_param("s", $exam_id);
    $stmt->execute();
    $stmt->bind_result($exam_name);
    $stmt->fetch();
    $stmt->close();

    $questions_data = [];
    $questions_result = $conn->query("SELECT * FROM questions WHERE exam_id = '$exam_id'");
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
    $questions_data = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create & Manage Exam</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5; /* Dirty White */
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            margin-bottom: 20px;
            color: #9ACD32; /* Yellow-Green */
        }

        .help-button a {
            color: #9ACD32;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="hidden"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="text"][disabled] {
            background-color: #f1f1f1;
            border: none;
            color: #555;
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
            padding: 10px;
            background: #fff;
            border-radius: 4px;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
        }

        .choice-label {
            width: 20px;
            font-weight: bold;
            margin-right: 10px;
        }

        .remove-question-button, .add-question-button, .add-choice-button, .back-button {
            background-color: #9ACD32; /* Yellow-Green */
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
        }

        .remove-question-button {
            background-color: #dc3545;
        }

        .remove-question-button:hover, .add-question-button:hover, .add-choice-button:hover {
            opacity: 0.8;
        }

        .success-message {
            background-color: #e9f7df;
            color: #4F8A10;
            border: 1px solid #c6e3b6;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Create & Manage Exam</h1>
        <p><b>Important Note:</b> Make sure to enter a name for your exam first then save it before adding questions.
           <br>Then go back to your Dashboard, enter your created exam and add your questions and choices.
           <br><i><b>(Always remember to save your exam!)</b></i>
        </p>

        <div class="help-button"><p>Having errors? <a href="help">Click here.</a></p></div>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <a href="dashboard.php" class="back-button">Go Back to Dashboard</a>

        <form method="post" action="">
            <label for="exam_name">Exam Name:</label>
            <input type="text" id="exam_name" name="exam_name" value="<?php echo htmlspecialchars($exam_name); ?>" required>

            <label for="exam_id_display">Exam ID:</label>
            <input type="text" id="exam_id_display" value="<?php echo htmlspecialchars($exam_id); ?>" disabled>
            <input type="hidden" id="exam_id" name="exam_id" value="<?php echo htmlspecialchars($exam_id); ?>">

            <div id="questions-container">
                <?php foreach ($questions_data as $index => $question): ?>
                    <div class="question-container" data-index="<?php echo $index; ?>">
                        <label for="question_text_<?php echo $index; ?>">Question <?php echo $index + 1; ?>:</label>
                        <input type="text" id="question_text_<?php echo $index; ?>" name="questions[<?php echo $index; ?>][question_text]" value="<?php echo htmlspecialchars($question['question_text']); ?>" required>
                        <input type="hidden" name="questions[<?php echo $index; ?>][id]" value="<?php echo htmlspecialchars($question['id']); ?>">

                        <div class="choices-container" id="choices-container_<?php echo $index; ?>">
                            <?php foreach ($question['choices'] as $key => $choice): ?>
                                <div class="choice-container">
                                    <span class="choice-label"><?php echo chr(65 + $key); ?>:</span>
                                    <input type="text" name="questions[<?php echo $index; ?>][choices][<?php echo $key; ?>][choice_text]" value="<?php echo htmlspecialchars($choice['choice_text']); ?>">
                                    <input type="checkbox" name="questions[<?php echo $index; ?>][choices][<?php echo $key; ?>][is_correct]" <?php echo $choice['is_correct'] ? 'checked' : ''; ?>> Correct
                                    <input type="hidden" name="questions[<?php echo $index; ?>][choices][<?php echo $key; ?>][id]" value="<?php echo htmlspecialchars($choice['id']); ?>">
                                    <button type="button" class="remove-choice-button" onclick="removeChoice(this)">Remove Choice</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="add-choice-button" onclick="addChoice(<?php echo $index; ?>)">Add Choice</button>
                        <button type="button" class="remove-question-button" onclick="removeQuestion(this)">Remove Question</button>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-question-button" class="add-question-button">Add Question</button>
            <input type="submit" value="Save Exam" class="add-question-button">
        </form>
    </div>

<script>
    function addQuestion() {
        var questionsContainer = document.getElementById('questions-container');
        var questionCount = questionsContainer.children.length;
        var newQuestionContainer = document.createElement('div');
        newQuestionContainer.classList.add('question-container');
        newQuestionContainer.setAttribute('data-index', questionCount);
        newQuestionContainer.innerHTML = `
            <label for="question_text_${questionCount}">Question ${questionCount + 1}:</label>
            <input type="text" id="question_text_${questionCount}" name="questions[${questionCount}][question_text]" required>
            <div class="choices-container" id="choices-container_${questionCount}">
            </div>
            <button type="button" class="add-choice-button" onclick="addChoice(${questionCount})">Add Choice</button>
            <button type="button" class="remove-question-button" onclick="removeQuestion(this)">Remove Question</button>
        `;
        questionsContainer.appendChild(newQuestionContainer);
    }

    function removeQuestion(button) {
        button.parentElement.remove();
    }

    function addChoice(questionIndex) {
        var choicesContainer = document.getElementById(`choices-container_${questionIndex}`);
        var choiceCount = choicesContainer.children.length;
        var newChoiceContainer = document.createElement('div');
        newChoiceContainer.classList.add('choice-container');
        newChoiceContainer.innerHTML = `
            <span class="choice-label">${String.fromCharCode(65 + choiceCount)}:</span>
            <input type="text" name="questions[${questionIndex}][choices][${choiceCount}][choice_text]">
            <input type="checkbox" name="questions[${questionIndex}][choices][${choiceCount}][is_correct]"> Correct
            <button type="button" class="remove-choice-button" onclick="removeChoice(this)">Remove Choice</button>
        `;
        choicesContainer.appendChild(newChoiceContainer);
    }

    function removeChoice(button) {
        button.parentElement.remove();
    }

    document.getElementById('add-question-button').addEventListener('click', addQuestion);
</script>
</body>
</html>
