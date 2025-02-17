<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <title>Login</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #293CB7;
            font-family: 'Arial', sans-serif;
            margin: 0;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.15); /* Lighter transparent background */
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(5px); /* Subtle blur */
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }

        h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: white;
        }

        .input-group-text {
            background-color: rgba(255, 255, 255, 0.3);
            border-right: none;
            color: white;
        }

        .form-control {
            border-left: none;
            box-shadow: none;
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
        }

        .form-control:focus {
            box-shadow: 0 0 5px rgba(41, 60, 183, 0.6);
            border-color: #293CB7;
            background-color: rgba(255, 255, 255, 0.4);
        }

        .btn-primary {
            background-color: #293CB7;
            border: none;
            transition: 0.3s;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1e2f91;
        }

        .register-text {
            margin-top: 12px;
            font-size: 14px;
            color: white;
        }

        .register-text a {
            color: #FFD700; /* Yellow color for better visibility */
            font-weight: bold;
            text-decoration: none;
        }

        .register-text a:hover {
            text-decoration: underline;
        }

        .alert-success {
            background-color: #28a745;
            color: white;
            font-weight: bold;
        }

        /* For better visibility on small screens */
        @media (max-width: 768px) {
            .login-container {
                max-width: 90%;
            }
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2>Login</h2>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success mt-4" role="alert">
                Registration successfully completed! <a href="login.php" class="btn btn-link text-white">Proceed to Login</a>
            </div>
        <?php endif; ?>

        <form action="process_login.php" method="POST">

            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>

            <p class="register-text">Don't have an account? <a href="register.php">Register here</a></p>

        </form>
    </div>

</body>
</html>
