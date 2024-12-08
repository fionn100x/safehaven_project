<?php

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and protect against SQL injection
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // SQL query to insert data into the users table
    $sql = "INSERT INTO users (first_name, last_name, email, password) 
            VALUES ('$first_name', '$last_name', '$email', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        // Registration successful, redirect to the success page
        header("Location: register_success.php");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
