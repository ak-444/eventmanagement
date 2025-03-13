<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <title>Register</title>
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
    }

    h2 {
      font-size: 24px;
      margin-bottom: 20px;
      color: #fff;
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
      text-align: center;
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
    <h2>Register</h2>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
      <div class="alert alert-success mt-4" role="alert">
        Registration successfully completed! <a href="login.php" class="btn btn-link text-white">Proceed to Login</a>
      </div>
    <?php endif; ?>

    <form action="process_register.php" method="POST" autocomplete="off">

      <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" class="form-control" id="email" name="email" placeholder="Email" autocomplete="off" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="user_type" class="form-label">User Type</label>
        <select class="form-select" id="user_type" name="user_type" required>
          <option value="user">User</option>
          <option value="staff">Staff</option>
          <option value="admin">Admin</option>
        </select>
      </div>

      <div class="mb-3">
        <label for="school_id" class="form-label">School ID</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-card-text"></i></span>
          <input type="text" class="form-control" id="school_id" name="school_id" placeholder="Enter School ID" required>
        </div>
      </div>

      <div class="mb-3">
        <label for="department" class="form-label">Department</label>
        <select class="form-select" id="department" name="department" required>
          <option value="Information Technology">Information Technology</option>
          <option value="Accounting">Accounting</option>
          <option value="Business">Business</option>
        </select>
      </div>

      <button type="submit" class="btn btn-primary w-100">Register</button>

      <p class="register-text">Already have an account? <a href="login.php">Login here</a></p>

    </form>
  </div>

</body>
</html>