<?php
// Include database connection file
global $conn;
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'db'; // Your database name
// Make it globally accessible
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Connection successful

session_start(); // Start session for tracking login

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // SQL query to fetch user from the database
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Password is correct, log the user in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['first_name'] = $user['first_name'];
            header("Location: dashboard.php"); // Redirect to dashboard (or any other page)
            exit();
        } else {
            // Incorrect password, redirect with error
            header("Location: login.php?error=invalid_credentials");
            exit();
        }
    } else {
        // No user found with that email, redirect with error
        header("Location: login.php?error=invalid_credentials");
        exit();
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
