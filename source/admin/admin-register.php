<?php
// Include the database connection file
include("../conn.php");

// Define a variable to store the message
$message = "";

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get data from the form
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate the form data
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } else {
        // Hash the password using bcrypt
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Check if the email is already registered
        $email_check_sql = "SELECT * FROM Admins WHERE email = ?";
        $stmt = $conn->prepare($email_check_sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            // Prepare the SQL query to insert the new admin
            $sql = "INSERT INTO Admins (email, password) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $email, $hashed_password);

            // Execute the query
            if ($stmt->execute()) {
                $message = "Registration successful!";
            } else {
                $message = "Error: " . $stmt->error;
            }
        }

        // Close the statement
        $stmt->close();
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Registration Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-register.css">
</head>
<body>
    <!-- Popup message -->
    <?php if ($message): ?>
        <div class="popup-message <?php echo strpos($message, 'successful') !== false ? '' : 'error'; ?>" id="popupMessage">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="container">
        <h1>Admin Registration</h1>
        <form action="admin-register.php" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <div class="text-center mt-3">
                            <small>Already have an admin? <a href="../login.php" class="text-decoration-none">Login here</a></small>
                        </div>
    </div>

    <script>
        // Show the popup message if it's set
        <?php if ($message): ?>
            document.getElementById('popupMessage').style.display = 'block';
            setTimeout(function () {
                document.getElementById('popupMessage').style.display = 'none';
            }, 5000); // Hide the message after 5 seconds
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
