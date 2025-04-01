<?php
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
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
            padding: 12px 20px;
            text-decoration: none;
            color: #f0f0f0;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        .sidebar a i {
            margin-right: 10px;
            font-size: 18px;
        }
        .sidebar a:hover {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #fff;
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
        }

        /* Dashboard Cards */
        .dashboard-card {
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease-in-out;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .dashboard-card h5 {
            font-weight: bold;
        }

        /* Buttons */
        .btn-custom {
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>AU JAS</h4>
        <a href="dashboard.php" onclick="changeHeader('Dashboard')"><i class="bi bi-house-door"></i> Dashboard</a>
        <a href="Event Calendar.php" onclick="changeHeader('Event Calendar')"><i class="bi bi-calendar"></i> Event Calendar</a>
        <a href="Event Management.php" onclick="changeHeader('Event Management')"><i class="bi bi-gear"></i> Event Management</a>
        <a href="#" onclick="changeHeader('User Management')"><i class="bi bi-people"></i> User Management</a>
        <a href="#" onclick="changeHeader('Reports')"><i class="bi bi-file-earmark-text"></i> Reports</a>
    </div>

    <div class="content">
        <!-- Navbar -->
        <nav class="navbar navbar-light">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1" id="headerTitle">Dashboard</span>
                
                <!-- User Info -->
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="#">User Type: <?php echo htmlspecialchars($_SESSION['user_type']); ?></a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Content Section -->
        <section>
            <div class="alert alert-info" role="alert">
                Breaking News: The ICT Department’s event is scheduled for 1/22/2025! Don’t miss it.
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
                <table class="table table-borderless">
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
