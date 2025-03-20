<?php

session_start();
require_once 'config.php'; // Include the config file
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
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
    <title>Dashboard</title>
    <style>
        body {
            display: flex;
            background: #f4f4f4;
        }

        /* Sidebar */
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

        /* Main content */
        .content {
            margin-left: 270px;
            padding: 20px;
            width: 100%;
        }

        /* Navbar */
        .navbar {
            background-color: #ffffff;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
            box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        .navbar .dropdown-menu {
            min-width: 200px;
        }

        /* Dashboard Cards */
        .dashboard-card {
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
            cursor: pointer;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.2);
        }
        .dashboard-card h5 {
            font-weight: bold;
        }

        /* Table */
        .table thead th {
            background: #293CB7;
            color: white;
        }

        /* Responsive Fix */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }
            .content {
                margin-left: 210px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <!-- Navbar -->
        <nav class="navbar navbar-light">
            <div class="container-fluid d-flex justify-content-between">
                <span class="navbar-brand mb-0 h1" id="headerTitle">Dashboard</span>
                
                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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

        <!-- Content Section -->
        <section>
            <div class="alert alert-info" role="alert">
                <strong>Breaking News:</strong> The ICT Department’s event is scheduled for 1/22/2025! Don’t miss it.
            </div>
        </section>

        <!-- Dashboard Cards Section -->
        <section class="my-5">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="dashboard-card bg-primary text-white">
                            <h5>Total Events</h5>
                            <p>Click to View</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card bg-success text-white">
                            <h5>Upcoming Events</h5>
                            <p>Click to View</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card bg-danger text-white">
                            <h5>Cancelled Bookings</h5>
                            <p>Click to View</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- New Bookings Section -->
        <section class="new-bookings-table">
            <div class="container">
                <h2 class="mb-4">New Bookings</h2>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Department</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>ICT Department</td>
                            <td>1/22/2025</td>
                            <td><a href="#" class="text-danger fw-bold">Delete</a></td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>CAS Department</td>
                            <td>1/23/2025</td>
                            <td><a href="#" class="text-danger fw-bold">Delete</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        function changeHeader(title) {
            document.getElementById('headerTitle').textContent = title;
        }
    </script>
</body>
</html>