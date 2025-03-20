<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
$user_type = $_SESSION['user_type'] ?? 'user';
$dashboardLink = match($user_type) {
    'admin' => 'admin_dashboard.php',
    'staff' => 'staff_dashboard.php',
    default => 'user_dashboard.php'
};

// Fetch approved events from the database
$sql = "SELECT id, event_name FROM events WHERE status='Approved'";
$result = $conn->query($sql);
$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $questions = $_POST['questions'];

    if (empty($event_id) || empty($title)) {
        echo "<script>alert('Please fill in all required fields.'); window.history.back();</script>";
        exit();
    }

    if (empty($questions)) {
        echo "<script>alert('Please add at least one question.'); window.history.back();</script>";
        exit();
    }

    // Insert questionnaire into the database
    $stmt = $conn->prepare("INSERT INTO questionnaires (event_id, title, description, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $event_id, $title, $description);
    $stmt->execute();
    $questionnaire_id = $stmt->insert_id;
    $stmt->close();

    // Insert questions into the database
    foreach ($questions as $question_text) {
        if (!empty($question_text)) {
            $stmt = $conn->prepare("INSERT INTO questions (questionnaire_id, question_text, question_type) VALUES (?, ?, 'likert')");
            $stmt->bind_param("is", $questionnaire_id, $question_text);
            $stmt->execute();
            $stmt->close();
        }
    }

    echo "<script>alert('Questionnaire added successfully!'); window.location.href='admin_questionnaires.php';</script>";
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>Add Questionnaire</title>
    <style>
        body {
            display: flex;
            background: #f4f4f4;
        }
        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #293CB7, #1E2A78);
            padding-top: 20px;
            position: fixed;
            color: #ffffff;
            box-shadow: 4px 0px 10px rgba(0, 0, 0, 0.2);
            z-index: 1000;
        }
        .sidebar h4 {
            text-align: center;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #f0f0f0;
            font-size: 16px;
            transition: background 0.3s ease, border-left 0.3s ease;
        }
        .sidebar a i {
            margin-right: 10px;
            font-size: 18px;
        }
        .sidebar a:hover, 
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 5px solid #fff;
        }
        .content {
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .main-container {
            display: flex;
            gap: 20px;
        }
        .form-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            flex: 1;
            max-width: 60%;
        }
        .question-list-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            flex: 1;
            max-width: 35%;
        }
        .question-input {
            margin-bottom: 15px;
        }
        .question-item {
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 10px;
            position: relative;
        }
        .question-actions {
            position: absolute;
            right: 10px;
            top: 10px;
        }
        .question-text {
            margin-right: 70px;
            word-break: break-word;
        }
        .no-questions {
            color: #6c757d;
            text-align: center;
            font-style: italic;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
        
    

    <div class="content">
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Add Questionnaire</span>
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">User Type: <?php echo htmlspecialchars($_SESSION['user_type']); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="main-container">
            <!-- Form Container - Left Side -->
            <div class="form-container">
                <form method="POST" action="add_questionnaire.php" id="questionnaireForm">
                    <div class="mb-3">
                        <label for="event_id" class="form-label">Select Event</label>
                        <select class="form-control" id="event_id" name="event_id" required>
                            <option value="">-- Select an Event --</option>
                            <?php foreach ($events as $event) : ?>
                                <option value="<?= $event['id'] ?>"><?= htmlspecialchars($event['event_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Questionnaire Title</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <!-- Likert Scale Options -->
                    <div class="mb-3">
                        <label class="form-label">Options</label>
                        <div>
                            <input type="text" class="form-control" name="likert_options[]" value="Strongly Agree" readonly>
                            <input type="text" class="form-control" name="likert_options[]" value="Agree" readonly>
                            <input type="text" class="form-control" name="likert_options[]" value="Neutral" readonly>
                            <input type="text" class="form-control" name="likert_options[]" value="Disagree" readonly>
                            <input type="text" class="form-control" name="likert_options[]" value="Strongly Disagree" readonly>
                        </div>
                    </div>
                    <div id="questions-container">
                        <div class="question-input">
                            <label for="question1" class="form-label">Question</label>
                            <div class="input-group mb-3">
                            <input type="text" class="form-control" id="question1" name="temp_question">
                                <button class="btn btn-outline-success" type="button" onclick="addQuestionToList()">Add</button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Questionnaire</button>
                </form>
            </div>

            <!-- Question List Container - Right Side -->
            <div class="question-list-container">
                <h5>Question List</h5>
                <div id="question-list">
                    <div class="no-questions">No questions added yet</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let questionCount = 0;
        const questions = [];

        function addQuestionToList() {
            const questionInput = document.getElementById('question1');
            const questionText = questionInput.value.trim();
            
            if (questionText === '') {
                alert('Please enter a question');
                return;
            }
            
            // Clear any previous error messages
            const errorMessages = document.getElementsByClassName('error-message');
            while(errorMessages.length > 0) {
                errorMessages[0].parentNode.removeChild(errorMessages[0]);
            }
            
            // Rest of your existing code...
            questionCount++;
            questions.push({
                id: questionCount,
                text: questionText
            });
            
            updateQuestionList();
            questionInput.value = '';
            questionInput.focus();
        }

        function updateQuestionList() {
            const questionList = document.getElementById('question-list');
            
            if (questions.length === 0) {
                questionList.innerHTML = '<div class="no-questions">No questions added yet</div>';
                return;
            }
            
            let html = '';
            questions.forEach((question, index) => {
                html += `
                    <div class="question-item" data-id="${question.id}">
                        <div class="question-text">${index + 1}. ${escapeHtml(question.text)}</div>
                        <div class="question-actions">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="editQuestion(${question.id})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(${question.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <input type="hidden" name="questions[]" value="${escapeHtml(question.text)}" form="questionnaireForm">
                    </div>
                `;
            });
            
            questionList.innerHTML = html;
        }

        function editQuestion(id) {
            const question = questions.find(q => q.id === id);
            if (!question) return;
            
            const newText = prompt('Edit question:', question.text);
            if (newText !== null && newText.trim() !== '') {
                question.text = newText.trim();
                updateQuestionList();
            }
        }

        function deleteQuestion(id) {
            if (confirm('Are you sure you want to delete this question?')) {
                const index = questions.findIndex(q => q.id === id);
                if (index !== -1) {
                    questions.splice(index, 1);
                    updateQuestionList();
                }
            }
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
</body>
</html>