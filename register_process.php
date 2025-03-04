<?php

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
            // Get the user ID of the newly inserted user
            $user_id = mysqli_insert_id($conn);

            // Create full_name from first_name and last_name
            $full_name = $first_name . ' ' . $last_name;

            // Insert default data into the profiles table without user_id
            $profile_sql = "INSERT INTO user_profiles (user_id, first_name, last_name, full_name, birthday, bio, likes, dislikes, friends, meditations, journals, blossoms, level, profile_pic)
                            VALUES ('$user_id', '$first_name', '$last_name', '$full_name', '', 'This is my bio!', 'None.', 'None.', 0, 0, 0, 0, 0, 'pictures/no_profile.jpg')";

            if (mysqli_query($conn, $profile_sql)) {
                // Registration successful, redirect to the success page
                header("Location: register_success.php");
                exit();
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
