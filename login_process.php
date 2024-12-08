<?php
// Include database connection file
$host = 'localhost'; // XAMPP runs MySQL on localhost
$username = 'root'; // Default MySQL username in XAMPP
$password = ''; // Default MySQL password is empty in XAMPP
$dbname = 'db'; // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Connection successful
echo "Connected successfully";

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
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No user found with that email!";
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
