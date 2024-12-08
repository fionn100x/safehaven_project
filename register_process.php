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

// If the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data and protect against SQL injection
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    if (!preg_match('/[A-Z]/', $password)) {
        // No capital letter in password
        header("Location: register.php?error=password_capital");
        exit();
    } elseif (strlen($password) < 8) {
        // Password less than 8 characters
        header("Location: register.php?error=password_length");
        exit();
    } elseif (!preg_match('/[0-9]/', $password)) {
        // No number in password
        header("Location: register.php?error=password_number");
        exit();
    }

    // Check if the email already exists in the database
    $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmailQuery);

    // If the email already exists
    if (mysqli_num_rows($result) > 0) {
        // Redirect to register.php with an error message
        header("Location: register.php?error=email_exists");
        exit();
    } else {
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
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
