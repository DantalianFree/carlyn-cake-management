<?php

// Include the database connection file
include("../conn.php");

// Define a variable to store the message
$message = "";

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get data from the form
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Validate the form data
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($confirm_password)) {
        die("All fields are required.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Hash the password using bcrypt
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the email is already registered
    $email_check_sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($email_check_sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        die("This email is already registered.");
    }

    // Prepare the SQL query to insert the new user
    $sql = "INSERT INTO users (first_name, last_name, email, password) 
            VALUES (?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $first_name, $last_name, $email, $hashed_password);

    // Execute the query
    if ($stmt->execute()) {
        $message = "Registration successful!";  // Set the success message
    } else {
        $message = "Error: " . $stmt->error;   // Set the error message
    }

    // Close the statement and the connection
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
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
                        <h1 class="text-center text-primary">Register</h1>

                        <!-- Popup Message -->
                        <?php if ($message): ?>
                            <div class="alert alert-success text-center" role="alert" id="popupMessage">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Registration Form -->
                        <form action="user-register.php" method="POST">
                            <div class="mb-3">
                                <label for="first_name" class="form-label">First Name:</label>
                                <input type="text" id="first_name" name="first_name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="last_name" class="form-label">Last Name:</label>
                                <input type="text" id="last_name" name="last_name" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" id="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label for="confirm-password" class="form-label">Confirm Password:</label>
                                <input type="password" id="confirm-password" name="confirm-password" class="form-control" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Register</button>
                        </form>

                        <!-- Login Link -->
                        <div class="text-center mt-3">
                            <small>Already have an account? <a href="user-login.php" class="text-decoration-none">Login here</a></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
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

