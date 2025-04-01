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
      min-height: 100vh;
      background-color: #293CB7;
      font-family: 'Arial', sans-serif;
      margin: 0;
      flex-direction: column;
      padding: 20px;
      overflow: hidden;
    }

    .header {
      text-align: center;
      margin-bottom: 15px;
      color: white;
    }

    .header h1 {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 0;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
      letter-spacing: 1px;
    }

    .header .subtitle {
      font-size: 0.9rem;
      opacity: 0.9;
      margin-top: 5px;
      font-weight: 300;
    }

    .login-container { 
      background: rgba(255, 255, 255, 0.15);
      padding: 25px;
      border-radius: 15px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
      width: 100%;
      max-width: 400px;
      backdrop-filter: blur(5px);
      border: 1px solid rgba(255, 255, 255, 0.3);
      overflow: hidden;
    }

    .login-content {
      max-height: calc(100vh - 200px);
      overflow-y: auto;
      padding-right: 5px;
    }

    .login-content::-webkit-scrollbar {
      width: 0;
      background: transparent;
    }

    h2 {
      font-size: 20px;
      margin-bottom: 15px;
      color: #fff;
      text-align: center;
    }

    .form-label {
      color: white;
      margin-bottom: 3px;
      display: block;
      font-size: 0.9rem;
    }

    .input-group-text {
      background-color: rgba(255, 255, 255, 0.3);
      border-right: none;
      color: white;
      padding: 0.375rem 0.75rem;
    }

    .form-control, .form-select {
      border-left: none;
      box-shadow: none;
      background-color: rgba(255, 255, 255, 0.3);
      color: white;
      padding: 0.375rem 0.75rem;
      font-size: 0.9rem;
      height: auto;
    }

    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.7);
      font-size: 0.9rem;
    }

    .form-control:focus, .form-select:focus {
      box-shadow: 0 0 5px rgba(41, 60, 183, 0.6);
      border-color: #293CB7;
      background-color: rgba(255, 255, 255, 0.4);
      color: white;
    }

    .form-select option {
      background-color: #293CB7;
      color: white;
    }

    .btn-primary {
      background-color: #293CB7;
      border: none;
      transition: 0.3s;
      color: white;
      margin-top: 8px;
      padding: 8px;
      font-size: 0.9rem;
    }

    .btn-primary:hover {
      background-color: #1E2B8F;
      transform: translateY(-1px);
    }

    .register-text {
      margin-top: 10px;
      font-size: 0.8rem;
      text-align: center;
      color: white;
    }

    .register-text a {
      color: #FFD700;
      font-weight: bold;
      text-decoration: none;
      transition: 0.2s;
    }

    .register-text a:hover {
      color: #FFC000;
      text-decoration: underline;
    }

    /* Success message styling */
    .alert-success {
      background-color: rgba(40, 167, 69, 0.85);
      color: white;
      border: none;
      border-radius: 8px;
      padding: 12px 15px;
      backdrop-filter: blur(5px);
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      animation: fadeIn 0.5s ease-in-out;
    }

    .alert-success .btn-link {
      color: white;
      text-decoration: underline;
      padding: 0;
      margin-left: 5px;
      font-weight: 600;
      transition: all 0.2s;
    }

    .alert-success .btn-link:hover {
      color: #e6ffe6;
      text-decoration: none;
    }

    /* File upload styling */
    .form-control-file {
      display: none;
    }

    .file-upload-label {
      display: block;
      padding: 8px 12px;
      background-color: rgba(255, 255, 255, 0.3);
      color: white;
      border-radius: 4px;
      cursor: pointer;
      text-align: center;
      transition: background-color 0.3s;
      font-size: 0.9rem;
    }

    .file-upload-label:hover {
      background-color: rgba(255, 255, 255, 0.4);
    }

    .file-name {
      color: rgba(255, 255, 255, 0.8);
      font-size: 0.8rem;
      margin-top: 5px;
      display: none;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
      .header h1 {
        font-size: 1.8rem;
      }
      
      .header .subtitle {
        font-size: 0.8rem;
      }
      
      .login-container {
        padding: 20px;
      }
      
      h2 {
        font-size: 1.1rem;
      }

      body {
        padding: 15px;
      }
    }
  </style>
</head>
<body>

  <div class="header">
    <h1>ARELLANO UNIVERSITY</h1>
    <div class="subtitle">Jose Abad Santos</div>
  </div>

  <div class="login-container">
    <div class="login-content">
      <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success mb-4" role="alert">
          Registration successfully completed! <a href="login.php" class="btn-link text-white">Proceed to Login</a>
        </div>
      <?php endif; ?>

      <h2>Register</h2>

      <form action="process_register.php" method="POST" autocomplete="off" enctype="multipart/form-data">
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
            <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
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
            <input type="text" class="form-control" id="school_id" name="school_id" placeholder="Enter your school ID number" required>
          </div>
        </div>

        <div class="mb-3">
          <label for="id_photo" class="form-label">School ID Photo (Verification)</label>
          <label for="id_photo" class="file-upload-label">
            <i class="bi bi-cloud-arrow-up"></i> Upload Photo of Your School ID
          </label>
          <input type="file" class="form-control-file" id="id_photo" name="id_photo" accept="image/*" required>
          <div class="file-name" id="file-name">No file chosen</div>
          <small class="text-white-50">Please upload a clear photo of your school ID for verification</small>
        </div>

        <div class="mb-3">
          <label for="department" class="form-label">Department</label>
          <select class="form-select" id="department" name="department" required>
            <option value="Information Technology">Information Technology</option>
            <option value="Accounting">Accounting</option>
            <option value="Business">Business</option>
          </select>
        </div>

        <button type="submit" class="btn btn-primary w-100 mt-2">Register</button>

        <p class="register-text mt-3">Already have an account? <a href="login.php">Login here</a></p>
      </form>
    </div>
  </div>

  <script>
    // Show selected file name
    document.getElementById('id_photo').addEventListener('change', function(e) {
      const fileName = document.getElementById('file-name');
      if (this.files.length > 0) {
        fileName.textContent = this.files[0].name;
        fileName.style.display = 'block';
      } else {
        fileName.style.display = 'none';
      }
    });
  </script>

</body>
</html>