<?php
session_start();
include 'config.php'; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL statement
    $sql = "SELECT id, username, password, user_type, status FROM users WHERE email=?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        // Bind result variables
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $hashed_password, $user_type, $status);
            $stmt->fetch();

            // Check if the user is still pending
            if ($status === 'pending') {
                echo "<!DOCTYPE html>
                      <html lang='en'>
                      <head>
                          <meta charset='UTF-8'>
                          <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                          <title>Pending Approval</title>
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
                              .message-container {
                                  background: rgba(255, 255, 255, 0.15);
                                  padding: 30px;
                                  border-radius: 15px;
                                  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                                  width: 100%;
                                  max-width: 400px;
                                  backdrop-filter: blur(5px);
                                  border: 1px solid rgba(255, 255, 255, 0.3);
                                  text-align: center;
                                  color: white;
                              }
                              h3 {
                                  font-size: 22px;
                                  margin-bottom: 15px;
                              }
                              p {
                                  font-size: 16px;
                                  margin-bottom: 15px;
                              }
                              .btn {
                                  display: inline-block;
                                  padding: 10px 15px;
                                  background-color: white;
                                  color: #293CB7;
                                  text-decoration: none;
                                  font-weight: bold;
                                  border-radius: 5px;
                                  border: 1px solid white;
                              }
                              .btn:hover {
                                  background-color: #FFD700;
                                  color: black;
                              }
                          </style>
                      </head>
                      <body>
                          <div class='message-container'>
                              <h3>Pending Approval</h3>
                              <p>Your account is still pending approval. Please wait for admin approval.</p>
                              <a href='login.php' class='btn'>Back to Login</a>
                          </div>
                      </body>
                      </html>";
                exit();
            }
            // Verify password
            elseif (password_verify($password, $hashed_password)) {
                // Store user details in session
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['user_type'] = $user_type;

                // Redirect based on user type
                redirectUser($user_type);
                exit();
            } else {
                echo "Invalid email or password.";
            }
        } else {
            echo "Invalid email or password.";
        }

        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }

    $conn->close();
}

// Define the redirect function
if (!function_exists('redirectUser')) {
    function redirectUser($user_type) {
        if ($user_type === 'admin') {
            header("Location: admin_dashboard.php");
        } elseif ($user_type === 'user') {
            header("Location: user_dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    }
}
?>