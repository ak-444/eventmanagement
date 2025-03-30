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
    $questions[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>View Questionnaire</title>
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
        .questionnaire-header {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .questionnaire-title {
            color: #293CB7;
            margin-bottom: 10px;
        }
        .questionnaire-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            color: #6c757d;
        }
        .questionnaire-meta span {
            display: flex;
            align-items: center;
        }
        .questionnaire-meta i {
            margin-right: 5px;
        }
        .questionnaire-description {
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .questions-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }
        .question-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: flex-start;
        }
        .question-item:last-child {
            border-bottom: none;
        }
        .question-number {
            font-weight: bold;
            margin-right: 10px;
            color: #293CB7;
            min-width: 30px;
        }
        .question-text {
            flex-grow: 1;
        }
        .no-questions {
            color: #6c757d;
            text-align: center;
            font-style: italic;
            padding: 20px;
        }
        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
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
                <span class="navbar-brand mb-0 h1">View Questionnaire</span>
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

        <div class="questionnaire-header">
            <h2 class="questionnaire-title"><?= htmlspecialchars($questionnaire['title']) ?></h2>
            <div class="questionnaire-meta">
                <span><i class="bi bi-calendar-event"></i> Event: <?= htmlspecialchars($questionnaire['event_name'] ?? 'N/A') ?></span>
                <span><i class="bi bi-calendar-date"></i> Created: <?= date('F j, Y', strtotime($questionnaire['created_at'])) ?></span>
                <span><i class="bi bi-list-check"></i> Questions: <?= count($questions) ?></span>
            </div>
            <?php if (!empty($questionnaire['description'])): ?>
                <div class="questionnaire-description">
                    <?= nl2br(htmlspecialchars($questionnaire['description'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="questions-container">
            <h4>Questions</h4>
            <?php if (!empty($questions)): ?>
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-item">
                        <div class="question-number"><?= $index + 1 ?>.</div>
                        <div class="question-text"><?= htmlspecialchars($question['question_text']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-questions">No questions found for this questionnaire</div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="admin_questionnaires.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Questionnaires
                </a>
                <a href="edit_questionnaire.php?id=<?= $questionnaire_id ?>" class="btn btn-warning text-white">
                    <i class="bi bi-pencil"></i> Edit Questionnaire
                </a>
            </div>
        </div>
    </div>
</body>
</html>