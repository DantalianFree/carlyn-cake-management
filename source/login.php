<?php
// Include the database connection file
include("source/conn.php");

// Start session
session_start();

// Define a variable to store the message
$message = "";

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get data from the form
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validate the form data
    if (empty($email) || empty($password)) {
        $message = "Both fields are required.";
    } else {
        // Check if the email exists in Admins table
        $sql_admin = "SELECT * FROM Admins WHERE email = ?";
        $stmt_admin = $conn->prepare($sql_admin);
        $stmt_admin->bind_param('s', $email);
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();

        if ($result_admin->num_rows > 0) {
            // Admin found
            $admin = $result_admin->fetch_assoc();
            if (password_verify($password, $admin['password'])) {
                // Set session variables for admin
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['email'] = $admin['email'];
                $_SESSION['role'] = 'admin';

                header("Location: admin-dashboard.php"); // Redirect to admin dashboard
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            // Check if the email exists in Users table
            $sql_user = "SELECT * FROM Users WHERE email = ?";
            $stmt_user = $conn->prepare($sql_user);
            $stmt_user->bind_param('s', $email);
            $stmt_user->execute();
            $result_user = $stmt_user->get_result();

            if ($result_user->num_rows > 0) {
                // User found
                $user = $result_user->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Set session variables for user
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = 'user';

                    header("Location: user/user-dashboard.php"); // Redirect to user dashboard
                    exit();
                } else {
                    $message = "Incorrect password.";
                }
            } else {
                $message = "No account found with that email.";
            }
        }
    }

    // Close the statements
    $stmt_admin->close();
    $stmt_user->close();
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Custom Pink Theme */
        body {
            background-color: #ffe6f0; /* Light pink background */
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            background-color: #fff;
            border-radius: 15px;
        }

        h1 {
            color: #d6336c; /* Bold pink */
        }

        .btn-primary {
            background-color: #d6336c;
            border-color: #d6336c;
        }

        .btn-primary:hover {
            background-color: #c2185b;
            border-color: #c2185b;
        }

        .text-primary {
            color: #d6336c !important;
        }

        a {
            color: #d6336c;
        }

        a:hover {
            color: #c2185b;
        }

        .alert-success {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5 shadow">
                    <div class="card-body">
                        <h1 class="text-center text-primary">Login</h1>

                        <!-- Popup Message -->
                        <?php if ($message): ?>
                            <div class="alert alert-success text-center" role="alert" id="popupMessage">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>

                        <!-- Registration Link -->
                        <div class="text-center mt-3">
                            <small>Don't have an account? <a href="user/user-register.php" class="text-decoration-none">Register here</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (Optional for Interactivity) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Automatically hide the popup message after 5 seconds
        <?php if ($message): ?>
            setTimeout(function() {
                document.getElementById('popupMessage').remove();
            }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>
