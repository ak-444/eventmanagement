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

// Get questionnaire ID from URL
$questionnaire_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($questionnaire_id <= 0) {
    header("Location: admin_questionnaires.php");
    exit();
}

// Initialize edited questions array
$edited_questions = $_SESSION['questionnaire_edits'][$questionnaire_id] ?? [];

// Fetch questionnaire details
$questionnaire = [];
$stmt = $conn->prepare("SELECT q.*, e.event_name 
                       FROM questionnaires q 
                       LEFT JOIN events e ON q.event_id = e.id 
                       WHERE q.id = ?");
$stmt->bind_param("i", $questionnaire_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $questionnaire = $result->fetch_assoc();
} else {
    header("Location: admin_questionnaires.php");
    exit();
}
$stmt->close();

// Fetch questions for this questionnaire
$questions = [];
$stmt = $conn->prepare("SELECT * FROM questions WHERE questionnaire_id = ? ORDER BY id");
$stmt->bind_param("i", $questionnaire_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // Use edited text if available
    if (isset($edited_questions[$row['id']])) {
        $row['question_text'] = $edited_questions[$row['id']];
    }
    $questions[] = $row;
}
$stmt->close();

// Fetch approved events from the database
$stmt = $conn->prepare("SELECT id, event_name FROM events WHERE status='Approved'");
$stmt->execute();
$result = $stmt->get_result();
$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $questions_to_add = $_POST['questions'] ?? [];
    $questions_to_keep = $_POST['existing_questions'] ?? [];
    $edited_questions = $_POST['edited_questions'] ?? [];
    
    // Debug - Print received data
    // echo "<pre>"; print_r($_POST); echo "</pre>"; exit;

    // Start transaction for atomic updates
    $conn->begin_transaction();

    try {
        // Update questionnaire in the database
        $stmt = $conn->prepare("UPDATE questionnaires 
                               SET event_id = ?, title = ?, description = ? 
                               WHERE id = ?");
        $stmt->bind_param("issi", $event_id, $title, $description, $questionnaire_id);
        $stmt->execute();
        $stmt->close();

        // Process existing questions
        if (!empty($questions_to_keep)) {
            // First update all edited questions
            foreach ($edited_questions as $question_id => $new_text) {
                $question_id = intval($question_id); // Ensure it's an integer
                $new_text = trim($new_text);
                if (!empty($new_text)) {
                    $stmt = $conn->prepare("UPDATE questions SET question_text = ? WHERE id = ? AND questionnaire_id = ?");
                    $stmt->bind_param("sii", $new_text, $question_id, $questionnaire_id);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // Then delete questions not in the keep list
            $placeholders = implode(',', array_fill(0, count($questions_to_keep), '?'));
            $types = str_repeat('i', count($questions_to_keep));
            $stmt = $conn->prepare("DELETE FROM questions 
                                   WHERE questionnaire_id = ? 
                                   AND id NOT IN ($placeholders)");
            $stmt->bind_param("i" . $types, $questionnaire_id, ...$questions_to_keep);
            $stmt->execute();
            $stmt->close();
        } else {
            // If no questions to keep, delete all existing questions
            $stmt = $conn->prepare("DELETE FROM questions WHERE questionnaire_id = ?");
            $stmt->bind_param("i", $questionnaire_id);
            $stmt->execute();
            $stmt->close();
        }

        // Add new questions
        foreach ($questions_to_add as $question_text) {
            $question_text = trim($question_text);
            if (!empty($question_text)) {
                $stmt = $conn->prepare("INSERT INTO questions (questionnaire_id, question_text, question_type) 
                                       VALUES (?, ?, 'text')");
                $stmt->bind_param("is", $questionnaire_id, $question_text);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Commit transaction
        $conn->commit();

        // Clear the session edits after successful save
        if (isset($_SESSION['questionnaire_edits'][$questionnaire_id])) {
            unset($_SESSION['questionnaire_edits'][$questionnaire_id]);
        }

        $_SESSION['success_message'] = "Questionnaire updated successfully!";
        header("Location: admin_questionnaires.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error updating questionnaire: " . $e->getMessage();
        header("Location: edit_questionnaire.php?id=" . $questionnaire_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>Edit Questionnaire</title>
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
        .existing-question {
            background-color: #e9f7ef;
        }
        .edit-highlight {
            animation: highlight 2s;
        }
        /* Add style for editable content */
        .editable-content {
            cursor: pointer;
            padding: 3px;
            border-radius: 3px;
        }
        .editable-content:hover {
            background-color: #f0f0f0;
        }
        .edit-mode {
            width: 100%;
            margin-right: 70px;
        }
        @keyframes highlight {
            0% { background-color: #ffff99; }
            100% { background-color: #e9f7ef; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>AU JAS</h4>
        
        <!-- Dashboard Link -->
        <a href="<?= $dashboardLink ?>" class="<?= ($current_page == $dashboardLink) ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i> Dashboard
        </a>

        <!-- Event Calendar (common for all) -->
        <a href="admin_Event Calendar.php" class="<?= ($current_page == 'admin_Event Calendar.php') ? 'active' : '' ?>">
            <i class="bi bi-calendar"></i> Event Calendar
        </a>

        <?php if($user_type == 'admin') : ?>
            <!-- Admin-only links -->
            <a href="admin_Event Management.php" class="<?= ($current_page == 'admin_Event Management.php') ? 'active' : '' ?>">
                <i class="bi bi-gear"></i> Event Management
            </a>
            <a href="admin_user management.php" class="<?= ($current_page == 'admin_user management.php') ? 'active' : '' ?>">
                <i class="bi bi-people"></i> User Management
            </a>
        <?php elseif($user_type == 'staff') : ?>
            <!-- Staff-only links -->
            <a href="staff_event_management.php" class="<?= ($current_page == 'staff_event_management.php') ? 'active' : '' ?>">
                <i class="bi bi-ticket-perforated"></i> Event Submissions
            </a>
        <?php endif; ?>

        <!-- Questionnaires (common for all) -->
        <a href="admin_questionnaires.php" class="<?= ($current_page == 'admin_questionnaires.php') ? 'active' : '' ?>">
            <i class="bi bi-clipboard"></i> Questionnaires
        </a>

        <!-- Reports (common for all) -->
        <a href="admin_reports.php" class="<?= ($current_page == 'reports.php') ? 'active' : '' ?>">
            <i class="bi bi-file-earmark-text"></i> Reports
        </a>
    </div>

    <div class="content">
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Edit Questionnaire</span>
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

        <?php if (isset($_SESSION['error_message'])) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="main-container">
            <!-- Form Container - Left Side -->
            <div class="form-container">
                <form method="POST" action="edit_questionnaire.php?id=<?= $questionnaire_id ?>" id="questionnaireForm">
                    <div class="mb-3">
                        <label for="event_id" class="form-label">Select Event</label>
                        <select class="form-control" id="event_id" name="event_id" required>
                            <option value="">-- Select an Event --</option>
                            <?php foreach ($events as $event) : ?>
                                <option value="<?= $event['id'] ?>" <?= $event['id'] == $questionnaire['event_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($event['event_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Questionnaire Title</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($questionnaire['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($questionnaire['description']) ?></textarea>
                    </div>
                    <div id="questions-container">
                        <div class="question-input">
                            <label for="question1" class="form-label">Add New Question</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="question1" name="temp_question">
                                <button class="btn btn-outline-success" type="button" onclick="addQuestionToList()">Add</button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Questionnaire</button>
                    <a href="admin_questionnaires.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>

            <!-- Question List Container - Right Side -->
            <div class="question-list-container">
                <h5>Question List</h5>
                <div id="question-list">
                    <?php if (empty($questions)): ?>
                        <div class="no-questions">No questions added yet</div>
                    <?php else: ?>
                        <?php foreach ($questions as $question): ?>
                            <div class="question-item existing-question" data-id="<?= $question['id'] ?>">
                                <div class="question-text editable-content" onclick="makeEditable(this, <?= $question['id'] ?>)"><?= htmlspecialchars($question['question_text']) ?></div>
                                <div class="question-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="editExistingQuestion(<?= $question['id'] ?>, '<?= addslashes($question['question_text']) ?>')">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteExistingQuestion(<?= $question['id'] ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="existing_questions[]" value="<?= $question['id'] ?>" form="questionnaireForm">
                                <!-- Always include an input for edited questions, even if not edited yet -->
                                <input type="hidden" name="edited_questions[<?= $question['id'] ?>]" 
                                       value="<?= htmlspecialchars($question['question_text']) ?>" 
                                       id="edited_question_<?= $question['id'] ?>"
                                       form="questionnaireForm">
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
            
            // First, keep all existing questions
            let html = '';
            const existingQuestions = document.querySelectorAll('.existing-question');
            existingQuestions.forEach(question => {
                html += question.outerHTML;
            });
            
            // Then add new questions
            if (questions.length > 0 || existingQuestions.length === 0) {
                if (existingQuestions.length === 0 && questions.length === 0) {
                    html = '<div class="no-questions">No questions added yet</div>';
                } else {
                    questions.forEach((question, index) => {
                        html += `
                            <div class="question-item" data-id="${question.id}">
                                <div class="question-text">${existingQuestions.length + index + 1}. ${escapeHtml(question.text)}</div>
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
                }
            }
            
            questionList.innerHTML = html;
        }

        // New function to make a question text directly editable
        function makeEditable(element, questionId) {
            // Check if already in edit mode
            if (element.querySelector('input')) return;
            
            const currentText = element.textContent;
            const input = document.createElement('input');
            input.type = 'text';
            input.value = currentText;
            input.className = 'form-control edit-mode';
            input.setAttribute('data-original', currentText);
            
            // Replace the text with the input
            element.innerHTML = '';
            element.appendChild(input);
            input.focus();
            
            // Add event listeners for saving on enter or blur
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    saveInlineEdit(element, input, questionId);
                }
            });
            
            input.addEventListener('blur', function() {
                saveInlineEdit(element, input, questionId);
            });
        }
        
        // Function to save the inline edit
        function saveInlineEdit(element, input, questionId) {
            const newText = input.value.trim();
            if (newText !== '' && newText !== input.getAttribute('data-original')) {
                // Update the displayed text
                element.textContent = newText;
                element.parentNode.classList.add('edit-highlight');
                
                // Update the hidden input value
                const hiddenInput = document.getElementById(`edited_question_${questionId}`);
                if (hiddenInput) {
                    hiddenInput.value = newText;
                }
                
                setTimeout(() => {
                    element.parentNode.classList.remove('edit-highlight');
                }, 2000);
            } else {
                // Restore original text if empty or unchanged
                element.textContent = input.getAttribute('data-original');
            }
        }

        function editQuestion(id) {
            const question = questions.find(q => q.id === id);
            if (!question) return;
            
            const newText = prompt('Edit question:', question.text);
            if (newText !== null) {
                const trimmedText = newText.trim();
                if (trimmedText !== '') {
                    question.text = trimmedText;
                    updateQuestionList();
                } else {
                    alert('Question text cannot be empty');
                }
            }
        }

        function editExistingQuestion(id, currentText) {
            const newText = prompt('Edit question:', currentText);
            if (newText !== null) {
                const trimmedText = newText.trim();
                if (trimmedText !== '') {
                    // Find the question in the DOM
                    const questionItem = document.querySelector(`.existing-question[data-id="${id}"]`);
                    if (questionItem) {
                        // Update the displayed text
                        const questionText = questionItem.querySelector('.question-text');
                        if (questionText) {
                            questionText.textContent = trimmedText;
                        }
                        
                        // Update the hidden input value
                        const hiddenInput = document.getElementById(`edited_question_${id}`);
                        if (hiddenInput) {
                            hiddenInput.value = trimmedText;
                        }
                        
                        // Add visual feedback
                        questionItem.classList.add('edit-highlight');
                        setTimeout(() => {
                            questionItem.classList.remove('edit-highlight');
                        }, 2000);
                    }
                } else {
                    alert('Question text cannot be empty');
                }
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

        function deleteExistingQuestion(id) {
            if (confirm('Are you sure you want to delete this question?')) {
                // Find the question in the DOM and remove it
                const questionItem = document.querySelector(`.existing-question[data-id="${id}"]`);
                if (questionItem) {
                    questionItem.remove();
                }
                
                // If no questions left, show the "no questions" message
                const questionList = document.getElementById('question-list');
                if (questionList.children.length === 0) {
                    questionList.innerHTML = '<div class="no-questions">No questions added yet</div>';
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