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
    $sql = "SELECT q.id, q.question_text 
            FROM questions q 
            JOIN questionnaires qn ON q.questionnaire_id = qn.id 
            WHERE qn.event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <title>Evaluation</title>
    <style>
        body {
            display: flex;
            background: #f8fafc;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Sidebar (unchanged) */
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
            padding: 30px;
            width: 100%;
        }

        /* Modern Navbar */
        .navbar {
            background-color: white;
            border-radius: 12px;
            padding: 15px 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
        }
        .navbar-brand {
            font-weight: 700;
            color: #1e293b;
            font-size: 1.5rem;
        }

        /* Modern Card Styling */
        .evaluation-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .evaluation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Form Elements */
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
        }

        /* Questions Styling */
        .question {
            margin-bottom: 25px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 12px;
            transition: background 0.3s;
        }
        .question:hover {
            background: #f1f5f9;
        }
        .question label {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
            display: block;
            font-size: 1.05rem;
        }
        .question textarea {
            min-height: 120px;
            resize: vertical;
        }

        /* Button Styling */
        .btn-primary {
            background-color: #6366f1;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #4f46e5;
            transform: translateY(-2px);
        }
        .btn-danger {
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
        }

        /* Alert Styling */
        .alert {
            border-radius: 12px;
            padding: 16px;
        }

        /* Modern Select Dropdown */
        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }

        /* User Info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-info span {
            font-weight: 500;
            color: #475569;
        }
    </style>
</head>
<body>
    <!-- Sidebar (unchanged) -->
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
        <!-- Modern Navbar -->
        <nav class="navbar animate__animated animate__fadeIn">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <span class="navbar-brand">Event Evaluation</span>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Event Selection Card -->
        <div class="evaluation-card animate__animated animate__fadeInUp">
            <h5 class="mb-4">Select Event to Evaluate</h5>
            <form method="GET" action="">
                <div class="mb-3">
                    <select class="form-select" id="event_id" name="event_id" onchange="this.form.submit()" required>
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

        <!-- Evaluation Questions Card -->
        <?php if ($event_id && !empty($questions)) : ?>
            <div class="evaluation-card animate__animated animate__fadeInUp">
                <h4 class="mb-4">Evaluation Questions</h4>
                <form method="POST" action="submit_evaluation.php">
                    <input type="hidden" name="event_id" value="<?= $event_id ?>">
                    <?php foreach ($questions as $index => $question) : ?>
                        <div class="question animate__animated animate__fadeIn" style="animation-delay: <?= $index * 0.05 ?>s">
                            <label for="question_<?= $question['id'] ?>">
                                <?= $index + 1 ?>. <?= htmlspecialchars($question['question_text']) ?>
                            </label>
                            <textarea class="form-control" 
                                      id="question_<?= $question['id'] ?>" 
                                      name="responses[<?= $question['id'] ?>]" 
                                      placeholder="Enter your response here..."
                                      required></textarea>
                        </div>
                    <?php endforeach; ?>
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send-fill me-2"></i> Submit Evaluation
                        </button>
                    </div>
                </form>
            </div>
        <?php elseif ($event_id && empty($questions)) : ?>
            <div class="alert alert-warning animate__animated animate__fadeIn">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> No questions found for this event.
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Add modern animations when elements come into view
        document.addEventListener('DOMContentLoaded', function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__fadeInUp');
                    }
                });
            }, {
                threshold: 0.1
            });

            document.querySelectorAll('.evaluation-card, .question').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>