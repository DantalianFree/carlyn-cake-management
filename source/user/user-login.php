<?php
// Include the database connection file
include("../conn.php");

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

                header("Location: user-dashboard.php"); // Redirect to user dashboard
                exit();
            } else {
                $message = "Incorrect password.";
            }
        } else {
            $message = "No account found with that email.";
        }

        // Close the statement
        $stmt_user->close();
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/user-login.css">
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card mt-5 shadow">
                    <div class="card-body">
                        <h1 class="text-center text-primary">User Login</h1>

                        <!-- Popup Message -->
                        <?php if ($message): ?>
                            <div class="alert alert-success text-center" role="alert" id="popupMessage">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form action="user-login.php" method="POST">
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
                            <small>Don't have an account? <a href="user-register.php" class="text-decoration-none">Register here</a></small>
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
