<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: admin_questionnaires.php");
    exit();
}

$questionnaire_id = $_GET['id'];

// Fetch questionnaire details
$stmt = $conn->prepare("SELECT * FROM questionnaires WHERE id = ?");
$stmt->bind_param("i", $questionnaire_id);
$stmt->execute();
$questionnaire = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$questionnaire) {
    header("Location: admin_questionnaires.php");
    exit();
}

// Fetch existing questions
$stmt = $conn->prepare("SELECT * FROM questions WHERE questionnaire_id = ?");
$stmt->bind_param("i", $questionnaire_id);
$stmt->execute();
$result = $stmt->get_result();
$existing_questions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch approved events
$sql = "SELECT id, event_name FROM events WHERE status='Approved'";
$result = $conn->query($sql);
$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $questions = $_POST['questions'];

    // Update questionnaire
    $stmt = $conn->prepare("UPDATE questionnaires SET event_id=?, title=?, description=? WHERE id=?");
    $stmt->bind_param("issi", $event_id, $title, $description, $questionnaire_id);
    $stmt->execute();
    $stmt->close();

    // Track existing question IDs
    $existing_ids = [];
    foreach ($existing_questions as $q) {
        $existing_ids[] = $q['id'];
    }

    $updated_ids = [];
    
    foreach ($questions as $question) {
        if (!empty($question['text'])) {
            if (isset($question['id']) && !empty($question['id'])) {
                // Update existing question
                $stmt = $conn->prepare("UPDATE questions SET question_text=?, question_type=? WHERE id=?");
                $stmt->bind_param("ssi", $question['text'], $question['type'], $question['id']);
                $updated_ids[] = $question['id'];
            } else {
                // Insert new question
                $stmt = $conn->prepare("INSERT INTO questions (questionnaire_id, question_text, question_type) VALUES (?, ?, ?)");
                $stmt->bind_param("iss", $questionnaire_id, $question['text'], $question['type']);
            }
            $stmt->execute();
            $stmt->close();
        }
    }

    // Delete removed questions
    $deleted_ids = array_diff($existing_ids, $updated_ids);
    if (!empty($deleted_ids)) {
        try {
            $conn->begin_transaction();
            
            // First delete associated answers
            $ids = implode(',', $deleted_ids);
            $conn->query("DELETE FROM answers WHERE question_id IN ($ids)");
            
            // Then delete the questions
            $conn->query("DELETE FROM questions WHERE id IN ($ids)");
            
            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            die("Error deleting questions: " . $e->getMessage());
        }
    }

    echo "<script>alert('Questionnaire updated successfully!'); window.location.href='admin_questionnaires.php';</script>";
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
        .main-container form {
            display: flex;
            gap: 20px;
            width: 100%;
            flex-wrap: wrap;
        }
        .form-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            flex: 2;
            min-width: 400px;
            max-width: 65%;
        }
        .question-list-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            flex: 1;
            max-width: 35%;
            height: calc(100vh - 200px);
            overflow-y: auto;
            position: sticky;
            top: 20px;
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

        .likert-stats {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .distribution-row {
            margin-bottom: 10px;
        }

        .distribution-row span {
            display: inline-block;
            width: 80px;
        }

        .progress {
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            background: #293CB7;
            transition: width 0.3s ease;
        }

        .response-question .badge {
            font-size: 0.75em;
            vertical-align: middle;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

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

        <div class="main-container">
            <form method="POST" action="edit_questionnaire.php?id=<?= $questionnaire_id ?>" id="questionnaireForm" class="d-flex gap-3 w-100">
                <!-- Left Column - Form Inputs -->
                <div class="form-container flex-grow-1">
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
                        <input type="text" class="form-control" id="title" name="title" required 
                               value="<?= htmlspecialchars($questionnaire['title']) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($questionnaire['description']) ?></textarea>
                    </div>
                    <div id="questions-container">
                        <div class="question-input">
                            <label class="form-label">Add Question</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" id="questionText" placeholder="Enter question">
                                <select class="form-select" id="questionType" style="max-width: 200px;">
                                    <option value="text">Open-ended</option>
                                    <option value="likert">Likert Scale (1-5)</option>
                                </select>
                                <button class="btn btn-outline-success" type="button" onclick="addQuestionToList()">
                                    <i class="bi bi-plus-lg"></i> Add
                                </button>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="bi bi-save"></i> Update Questionnaire
                    </button>
                </div>

                <!-- Right Column - Question List -->
                <div class="question-list-container">
                    <h5>Question List</h5>
                    <div id="question-list">
                        <?php if (empty($existing_questions)) : ?>
                            <div class="no-questions">No questions added yet</div>
                        <?php else : ?>
                            <?php foreach ($existing_questions as $index => $question) : ?>
                                <div class="question-item">
                                    <div class="question-text">
                                        <?= $index + 1 ?>. <?= htmlspecialchars($question['question_text']) ?>
                                        <span class="badge bg-secondary"><?= $question['question_type'] ?></span>
                                    </div>
                                    <div class="question-actions">
                                        <button class="btn btn-sm btn-outline-primary" type="button" onclick="editQuestion(event)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" type="button" onclick="deleteQuestion(event)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="questions[<?= $index ?>][id]" value="<?= $question['id'] ?>">
                                    <input type="hidden" name="questions[<?= $index ?>][text]" value="<?= htmlspecialchars($question['question_text']) ?>">
                                    <input type="hidden" name="questions[<?= $index ?>][type]" value="<?= $question['question_type'] ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Similar JavaScript as add_questionnaire.php with edit capabilities
        let questions = <?= json_encode(array_map(function($q) {
        return [
            'id' => $q['id'],
            'text' => $q['question_text'],
            'type' => $q['question_type']
        ];
    }, $existing_questions)) ?>;
        document.addEventListener('DOMContentLoaded', function() {
            updateQuestionList();
        });

        function updateQuestionList() {
            const list = document.getElementById('question-list');
            list.innerHTML = questions.length > 0 ? '' : '<div class="no-questions">No questions added yet</div>';

            questions.forEach((q, index) => {
                const questionEl = document.createElement('div');
                questionEl.className = 'question-item';
                questionEl.innerHTML = `
                    <div class="question-text">
                        ${index + 1}. ${escapeHtml(q.text)}
                        <span class="badge bg-secondary">${q.type}</span>
                    </div>
                    <div class="question-actions">
                        <button class="btn btn-sm btn-outline-primary" onclick="editQuestion(${q.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteQuestion(${q.id})">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <input type="hidden" name="questions[${index}][id]" value="${q.id || ''}">
                    <input type="hidden" name="questions[${index}][text]" value="${escapeHtml(q.text)}">
                    <input type="hidden" name="questions[${index}][type]" value="${q.type}">
                `;
                list.appendChild(questionEl);
            });
        }

        // Include the rest of the JavaScript functions from add_questionnaire.php
        // ... (same JavaScript as add_questionnaire.php with edit support) ...

        function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }


       let questionCount = 0;
        let editingQuestionId = null;

        function addQuestionToList() {
        const questionText = document.getElementById('questionText').value.trim();
        const questionType = document.getElementById('questionType').value;

        if (!questionText) {
            alert('Please enter a question');
            return;
        }

        questions.push({
            id: Date.now(),
            text: questionText,
            type: questionType
        });

        updateQuestionList();
        document.getElementById('questionText').value = '';
    }

    function updateQuestionList() {
        const list = document.getElementById('question-list');
        list.innerHTML = questions.length > 0 ? '' : '<div class="no-questions">No questions added yet</div>';
    
        questions.forEach((q, index) => {
            const questionEl = document.createElement('div');
            questionEl.className = 'question-item';
            questionEl.innerHTML = `
                <div class="question-text">
                    ${index + 1}. ${escapeHtml(q.text)}
                    <span class="badge bg-secondary">${q.type}</span>
                </div>
                <div class="question-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary" 
                            onclick="editQuestion(${q.id})">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" 
                            onclick="deleteQuestion(${q.id})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <input type="hidden" name="questions[${index}][id]" value="${q.id || ''}">
                <input type="hidden" name="questions[${index}][text]" value="${escapeHtml(q.text)}">
                <input type="hidden" name="questions[${index}][type]" value="${q.type}">
            `;
            list.appendChild(questionEl);
        });
    }

function editQuestion(id) {
    const question = questions.find(q => q.id === id);
    if (!question) return;

    // Populate modal fields
    document.getElementById('editQuestionText').value = question.text;
    document.getElementById('editQuestionType').value = question.type;
    editingQuestionId = id;
    
    // Show the modal
    const editModal = new bootstrap.Modal(document.getElementById('editQuestionModal'));
    editModal.show();
}

function saveEditedQuestion() {
    const newText = document.getElementById('editQuestionText').value.trim();
    const newType = document.getElementById('editQuestionType').value;

    if (!newText) {
        alert('Question text cannot be empty');
        return;
    }

    const questionIndex = questions.findIndex(q => q.id === editingQuestionId);
    if (questionIndex > -1) {
        questions[questionIndex].text = newText;
        questions[questionIndex].type = newType;
        updateQuestionList();
    }
    
    // Hide the modal
    bootstrap.Modal.getInstance(document.getElementById('editQuestionModal')).hide();
    editingQuestionId = null;
}

function deleteQuestion(id) {
    questions = questions.filter(q => q.id !== id);
    updateQuestionList();
}
    </script>

    <!-- Edit Question Modal -->
<div class="modal fade" id="editQuestionModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Question</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label for="editQuestionText" class="form-label">Question Text</label>
          <input type="text" class="form-control" id="editQuestionText" required>
        </div>
        <div class="mb-3">
          <label for="editQuestionType" class="form-label">Question Type</label>
          <select class="form-select" id="editQuestionType">
            <option value="text">Open-ended</option>
            <option value="likert">Likert Scale (1-5)</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="saveEditedQuestion()">Save Changes</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>