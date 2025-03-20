<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'staff') {
    header("Location: login.php");
    exit();
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
    <title>User Dashboard</title>
    <style>
        body {
            display: flex;
            background: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #293CB7, #1E2A78);
            padding-top: 20px;
            position: fixed;
            color: #ffffff;
            box-shadow: 4px 0px 10px rgba(0, 0, 0, 0.2);
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
        .sidebar a:hover, .sidebar a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 5px solid #fff;
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
            border-radius: 8px;
        }

        /* Dashboard Cards */
        .dashboard-card {
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
            text-align: center;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <!-- Navbar -->
        <nav class="navbar">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1">Dashboard</span>
                <div>
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </nav>

        <!-- Dashboard Cards -->
        <section class="my-5">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-6">
                        <div class="dashboard-card bg-primary text-white">
                            <h5>Total Events</h5>
                            <p>Click to View</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="dashboard-card bg-success text-white">
                            <h5>Upcoming Events</h5>
                            <p>Click to View</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</body>
</html>