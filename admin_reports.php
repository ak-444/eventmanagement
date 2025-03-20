<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_page = basename($_SERVER['PHP_SELF']);
include 'sidebar.php';

// Fetch all events for the dropdown
$events_sql = "SELECT id, event_name FROM events ORDER BY event_name ASC";
$events_result = $conn->query($events_sql);
$events = [];

if ($events_result && $events_result->num_rows > 0) {
    while ($event_row = $events_result->fetch_assoc()) {
        $events[] = $event_row;
    }
}

// Get data for the selected event
$selected_event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$questionnaires = [];

if ($selected_event_id > 0) {
    $sql = "SELECT q.id, q.title, q.description, q.created_at, e.event_name, 
           (SELECT COUNT(*) FROM questions WHERE questionnaire_id = q.id) AS question_count
           FROM questionnaires q
           JOIN events e ON q.event_id = e.id
           WHERE q.event_id = ?
           ORDER BY q.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $questionnaires[] = $row;
        }
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
    <title>Event Reports</title>
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --background-color: #f8f9fa;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-radius: 12px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        body {
            display: flex;
            background: var(--background-color);
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
            color: var(--text-color);
        }
        
        .sidebar {
            width: 280px;
            height: 100vh;
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            padding-top: 20px;
            position: fixed;
            color: #ffffff;
            box-shadow: var(--box-shadow);
            z-index: 1030;
        }
        
        .sidebar h4 {
            text-align: center;
            font-weight: 600;
            letter-spacing: 1px;
            margin-bottom: 30px;
        }
        
        .sidebar a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.85);
            font-size: 16px;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 8px 15px;
        }
        
        .sidebar a i {
            margin-right: 12px;
            font-size: 20px;
        }
        
        .sidebar a:hover, 
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            transform: translateX(5px);
        }
        
        .content {
            margin-left: 300px;
            padding: 30px;
            width: 100%;
        }
        
        .navbar {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 15px 20px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 20px;
            color: var(--primary-color);
        }
        
        .dropdown-toggle {
            border: 1px solid rgba(0,0,0,0.1);
            padding: 8px 16px;
            border-radius: var(--border-radius);
            font-weight: 500;
        }
        
        .dropdown-toggle:hover {
            background-color: rgba(0,0,0,0.03);
        }
        
        .dropdown-menu {
            border-radius: 8px;
            box-shadow: var(--box-shadow);
            border: none;
            padding: 10px;
        }
        
        .dropdown-item {
            border-radius: 6px;
            padding: 8px 12px;
        }
        
        .event-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background-color: var(--card-bg);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .card-title {
            margin-bottom: 0;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 20px;
        }
        
        .form-control {
            border-radius: var(--border-radius);
            padding: 12px 16px;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: none;
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
            border-color: var(--primary-color);
        }
        
        .btn {
            border-radius: var(--border-radius);
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn i {
            margin-right: 8px;
        }
        
        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        
        .table thead th {
            background-color: rgba(0,0,0,0.02);
            font-weight: 600;
            border-top: none;
            padding: 16px;
        }
        
        .table td {
            padding: 16px;
            vertical-align: middle;
        }
        
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0,0,0,0.01);
        }
        
        .report-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        
        .report-header h2 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .report-header h4 {
            color: var(--text-color);
            font-weight: 500;
        }
        
        .report-date {
            font-style: italic;
            text-align: right;
            margin-bottom: 30px;
            color: #6c757d;
        }
        
        .event-summary {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }
        
        .summary-card {
            flex: 1;
            min-width: 200px;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 20px;
            margin: 10px;
            box-shadow: var(--box-shadow);
            text-align: center;
        }
        
        .summary-card h5 {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .summary-card p {
            font-size: 24px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0;
        }
        
        .response-stats {
            margin-top: 30px;
            margin-bottom: 40px;
        }
        
        .response-question {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: var(--box-shadow);
        }
        
        .response-question h5 {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        
        .bar-chart {
            height: 25px;
            margin: 15px 0;
            background-color: rgba(0,0,0,0.03);
            border-radius: 100px;
            overflow: hidden;
        }
        
        .bar {
            height: 100%;
            float: left;
            text-align: center;
            color: white;
            font-weight: 600;
            line-height: 25px;
            transition: width 0.5s ease-in-out;
        }
        
        .bar-strongly-agree { background-color: #4cc9f0; }
        .bar-agree { background-color: #4895ef; }
        .bar-neutral { background-color: #4361ee; }
        .bar-disagree { background-color: #3f37c9; }
        .bar-strongly-disagree { background-color: #3a0ca3; }
        
        .legend {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 25px;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin: 5px 15px;
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            margin-right: 8px;
            border-radius: 3px;
        }
        
        .alert {
            border-radius: var(--border-radius);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        #printArea {
            background: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }
        
        @media print {
            .sidebar, .navbar, .event-header, .no-print {
                display: none !important;
            }
            .content {
                margin-left: 0;
                padding: 0;
                width: 100%;
            }
            #printArea {
                box-shadow: none;
                padding: 0;
            }
            body {
                background: white;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                width: 0;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show {
                width: 280px;
                transform: translateX(0);
            }
            .content {
                margin-left: 0;
                padding: 20px;
            }
            .toggle-sidebar {
                display: block;
            }
        }
    </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

    <div class="content">
        <nav class="navbar">
            <div class="container-fluid d-flex justify-content-between align-items-center">
                <span class="navbar-brand">Event Reports</span>
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#">User Type: <?php echo htmlspecialchars($_SESSION['user_type']); ?></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <div class="card mb-4">
            <div class="card-body">
                <div class="event-header">
                    <div class="d-flex align-items-center">
                        <form method="get" action="admin_reports.php" class="d-flex">
                            <select class="form-control me-3" name="event_id" id="event_id">
                                <option value="0">-- Select Event --</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?= $event['id'] ?>" <?= ($selected_event_id == $event['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($event['event_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Load Report
                            </button>
                        </form>
                    </div>
                    <div class="button-group">
                        <button class="btn btn-primary" onclick="printReport()">
                            <i class="bi bi-printer"></i> Print Report
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="printArea">
            <?php if ($selected_event_id > 0 && !empty($questionnaires)): ?>
                <div class="report-header">
                    <h2>Event Report</h2>
                    <h4><?= htmlspecialchars($questionnaires[0]['event_name']) ?></h4>
                </div>
                
                <div class="report-date">
                    Report generated on: <?= date('Y-m-d H:i:s') ?>
                </div>
                
                <div class="event-summary">
                    <?php
                        $total_questions = 0;
                        foreach ($questionnaires as $q) {
                            $total_questions += $q['question_count'];
                        }
                        
                        // Count total evaluations for this event
                        $eval_count_sql = "SELECT COUNT(DISTINCT user_id) as total_users FROM evaluations WHERE event_id = ?";
                        $stmt = $conn->prepare($eval_count_sql);
                        $stmt->bind_param("i", $selected_event_id);
                        $stmt->execute();
                        $eval_result = $stmt->get_result();
                        $eval_count = $eval_result->fetch_assoc();
                        $stmt->close();
                    ?>
                    <div class="summary-card">
                        <h5>Total Questionnaires</h5>
                        <p><?= count($questionnaires) ?></p>
                    </div>
                    <div class="summary-card">
                        <h5>Total Questions</h5>
                        <p><?= $total_questions ?></p>
                    </div>
                    <div class="summary-card">
                        <h5>Total Participants</h5>
                        <p><?= $eval_count['total_users'] ?? 0 ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Questionnaires</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Questionnaire Title</th>
                                        <th>Description</th>
                                        <th>Created On</th>
                                        <th>Questions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $counter = 1;
                                    foreach ($questionnaires as $row): ?>
                                        <tr>
                                            <td><?= $counter++ ?></td>
                                            <td><?= htmlspecialchars($row['title']) ?></td>
                                            <td><?= htmlspecialchars($row['description']) ?></td>
                                            <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                                            <td><span class="badge bg-primary rounded-pill"><?= $row['question_count'] ?> questions</span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <?php
                // Get all questions for this event
                $questions_sql = "SELECT q.id, q.question_text, q.questionnaire_id 
                                 FROM questions q
                                 JOIN questionnaires qn ON q.questionnaire_id = qn.id
                                 WHERE qn.event_id = ?
                                 ORDER BY q.questionnaire_id, q.id";
                
                $stmt = $conn->prepare($questions_sql);
                $stmt->bind_param("i", $selected_event_id);
                $stmt->execute();
                $questions_result = $stmt->get_result();
                
                if ($questions_result && $questions_result->num_rows > 0) {
                    echo '<div class="card mt-4">';
                    echo '<div class="card-header">';
                    echo '<h5 class="card-title">Response Statistics</h5>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    
                    echo '<div class="legend">';
                    echo '<div class="legend-item"><div class="legend-color bar-strongly-agree"></div>Strongly Agree</div>';
                    echo '<div class="legend-item"><div class="legend-color bar-agree"></div>Agree</div>';
                    echo '<div class="legend-item"><div class="legend-color bar-neutral"></div>Neutral</div>';
                    echo '<div class="legend-item"><div class="legend-color bar-disagree"></div>Disagree</div>';
                    echo '<div class="legend-item"><div class="legend-color bar-strongly-disagree"></div>Strongly Disagree</div>';
                    echo '</div>';
                    
                    echo '<div class="response-stats">';
                    
                    $question_number = 1;
                    while ($question = $questions_result->fetch_assoc()) {
                        // Get response counts for this question
                        $response_sql = "SELECT response, COUNT(*) as count 
                                        FROM evaluations 
                                        WHERE event_id = ? AND question_index = ? 
                                        GROUP BY response";
                        
                        $stmt2 = $conn->prepare($response_sql);
                        $stmt2->bind_param("ii", $selected_event_id, $question_number);
                        $stmt2->execute();
                        $response_result = $stmt2->get_result();
                        
                        // Initialize counts
                        $responses = [
                            'Strongly Agree' => 0,
                            'Agree' => 0,
                            'Neutral' => 0,
                            'Disagree' => 0,
                            'Strongly Disagree' => 0
                        ];
                        
                        $total_responses = 0;
                        
                        // Fill in actual counts
                        while ($response = $response_result->fetch_assoc()) {
                            if (isset($responses[$response['response']])) {
                                $responses[$response['response']] = $response['count'];
                                $total_responses += $response['count'];
                            }
                        }
                        
                        echo '<div class="response-question">';
                        echo '<h5>Question ' . $question_number . ': ' . htmlspecialchars($question['question_text']) . '</h5>';
                        echo '<p>Total Responses: <span class="badge bg-secondary rounded-pill">' . $total_responses . '</span></p>';
                        
                        if ($total_responses > 0) {
                            echo '<div class="bar-chart">';
                            foreach ($responses as $type => $count) {
                                $percentage = ($count / $total_responses) * 100;
                                $class = 'bar-' . strtolower(str_replace(' ', '-', $type));
                                echo '<div class="bar ' . $class . '" style="width: ' . $percentage . '%;">' . 
                                     ($percentage > 5 ? round($percentage) . '%' : '') . '</div>';
                            }
                            echo '</div>';
                            
                            echo '<div class="table-responsive mt-3">';
                            echo '<table class="table table-sm">';
                            echo '<thead><tr><th>Response</th><th>Count</th><th>Percentage</th></tr></thead>';
                            echo '<tbody>';
                            foreach ($responses as $type => $count) {
                                $percentage = ($count / $total_responses) * 100;
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($type) . '</td>';
                                echo '<td>' . $count . '</td>';
                                echo '<td>' . round($percentage, 1) . '%</td>';
                                echo '</tr>';
                            }
                            echo '</tbody></table>';
                            echo '</div>';
                        } else {
                            echo '<div class="alert alert-info">No responses yet for this question.</div>';
                        }
                        
                        echo '</div>';
                        $question_number++;
                        $stmt2->close();
                    }
                    
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                
                $stmt->close();
                
                // Get list of users who completed evaluations
                $users_sql = "SELECT DISTINCT e.user_id, u.username, COUNT(DISTINCT e.question_index) as questions_answered
                             FROM evaluations e
                             JOIN users u ON e.user_id = u.id
                             WHERE e.event_id = ?
                             GROUP BY e.user_id
                             ORDER BY u.username";
                
                $stmt = $conn->prepare($users_sql);
                $stmt->bind_param("i", $selected_event_id);
                $stmt->execute();
                $users_result = $stmt->get_result();
                
                if ($users_result && $users_result->num_rows > 0) {
                    echo '<div class="card mt-4">';
                    echo '<div class="card-header">';
                    echo '<h5 class="card-title">Evaluation Participants</h5>';
                    echo '</div>';
                    echo '<div class="card-body">';
                    echo '<div class="table-responsive">';
                    echo '<table class="table table-striped">';
                    echo '<thead><tr><th>#</th><th>User</th><th>Questions Answered</th></tr></thead>';
                    echo '<tbody>';
                    
                    $user_counter = 1;
                    while ($user = $users_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $user_counter++ . "</td>";
                        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                        echo "<td><span class='badge bg-primary rounded-pill'>" . $user['questions_answered'] . "</span></td>";
                        echo "</tr>";
                    }
                    
                    echo '</tbody></table>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                } else {
                    echo '<div class="alert alert-info mt-4">No evaluations have been submitted for this event yet.</div>';
                }
                
                $stmt->close();
                ?>
                
            <?php elseif ($selected_event_id > 0): ?>
                <div class="alert alert-warning">No questionnaires found for the selected event.</div>
            <?php else: ?>
                <div class="alert alert-info">Please select an event to view its report.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function printReport() {
            window.print();
        }
        
        // Auto-submit form when event is selected
        document.getElementById('event_id').addEventListener('change', function() {
            if (this.value > 0) {
                this.form.submit();
            }
        });
    </script>
</body>
</html>