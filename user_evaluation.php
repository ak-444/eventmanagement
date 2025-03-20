<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_type'], ['user', 'staff', 'admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch approved events from the database
$sql = "SELECT id, event_name FROM events WHERE status='Approved'";
$result = $conn->query($sql);
$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

// Fetch questions for the selected event
$event_id = $_GET['event_id'] ?? null;
$questions = [];
if ($event_id) {
    $sql = "SELECT q.question_text 
            FROM questions q 
            JOIN questionnaires qn ON q.questionnaire_id = qn.id 
            WHERE qn.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row['question_text'];
    }
    $stmt->close();
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
    <title>Evaluation</title>
    <style>
        body {
            display: flex;
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: linear-gradient(135deg, #293CB7, #1E2A78);
            color: white;
            padding-top: 20px;
            position: fixed;
            box-shadow: 4px 0px 10px rgba(0, 0, 0, 0.2);
        }
        .sidebar h4 {
            text-align: center;
            margin-bottom: 30px;
        }
        .sidebar a {
            display: block;
            padding: 12px 20px;
            text-decoration: none;
            color: white;
            font-size: 16px;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }

        /* Main Content */
        .content {
            margin-left: 260px;
            padding: 20px;
            width: 100%;
        }

        /* Navbar */
        .navbar {
            background-color: white;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
        }

        /* Evaluation Form */
        .evaluation-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .evaluation-form .question {
            margin-bottom: 20px;
        }
        .evaluation-form .question label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>AU JAS</h4>
        <a href="user_dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_dashboard.php' ? 'active' : '' ?>">
            <i class="bi bi-house-door"></i> Dashboard
        </a>
        <a href="user_eventCalendar.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_eventCalendar.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar"></i> Event Calendar
        </a>
        <a href="user_evaluation.php" class="<?= basename($_SERVER['PHP_SELF']) == 'user_evaluation.php' ? 'active' : '' ?>">
            <i class="bi bi-clipboard"></i> Evaluation
        </a>
    </div>

    <div class="content">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Evaluation</span>
                <div>
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Event Selection Form -->
        <div class="evaluation-form mt-4">
            <form method="GET" action="">
                <div class="mb-3">
                    <label for="event_id" class="form-label">Select Event to Evaluate</label>
                    <select class="form-control" id="event_id" name="event_id" onchange="this.form.submit()" required>
                        <option value="">-- Select an Event --</option>
                        <?php foreach ($events as $event) : ?>
                            <option value="<?= $event['id'] ?>" <?= $event_id == $event['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($event['event_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Evaluation Questions -->
        <?php if ($event_id && !empty($questions)) : ?>
            <div class="evaluation-form mt-4">
                <h4>Evaluation Questions</h4>
                <form method="POST" action="submit_evaluation.php">
                    <input type="hidden" name="event_id" value="<?= $event_id ?>">
                    <?php foreach ($questions as $index => $question) : ?>
                        <div class="question">
                            <label for="question<?= $index + 1 ?>"><?= $index + 1 ?>. <?= htmlspecialchars($question) ?></label>
                            <select class="form-control" id="question<?= $index + 1 ?>" name="responses[]" required>
                                <option value="">-- Select an Option --</option>
                                <option value="Strongly Agree">Strongly Agree</option>
                                <option value="Agree">Agree</option>
                                <option value="Neutral">Neutral</option>
                                <option value="Disagree">Disagree</option>
                                <option value="Strongly Disagree">Strongly Disagree</option>
                            </select>
                        </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary mt-3">Submit Evaluation</button>
                </form>
            </div>
        <?php elseif ($event_id && empty($questions)) : ?>
            <div class="alert alert-warning mt-4">No questions found for this event.</div>
        <?php endif; ?>
    </div>
</body>
</html>