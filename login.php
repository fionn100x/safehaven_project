<?php
// Initialize the error message variable
$error_message = '';

// Check if there's an error message for login
if (isset($_GET['error']) && $_GET['error'] == 'invalid_credentials') {
    $error_message = "Invalid email or password. Please try again.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
<header>
    <nav>
        <div class="banner">
            <a href="home.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="features.php">Features</a>
            <a href="contact.php">Contact Us</a>
            <a href="#" onclick="checkAuthentication('dashboard.php')">Dashboard</a>
            <a href="#" onclick="checkAuthentication('profile.php')">Profile Settings</a>
        </div>
    </nav>
</header>

<div class="left-section">
    <h1>LOGIN</h1>
    <p>Enter your email and password to log in.</p>
    <form action="login_process.php" method="POST">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="LOGIN">

        <p class="signup-link">Don't have an account? <a href="register.php">Sign up</a></p>
    </form>
</div>

<div class="right-section">
    <img src="safe_haven_text.png" alt="Safe Haven Text Logo" class="text-logo">
    <img src="safe_haven_logo.png" alt="Safe Haven Logo" class="logo">
    <div class="welcome-text">
        <h2>Welcome Back!</h2>
        <p>Access your mental well-being journey by logging in.</p>
    </div>
</div>

<!-- Modal for error message -->
<?php if ($error_message != ''): ?>
    <div class="modal" id="errorModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3>Error</h3>
            <p><?php echo $error_message; ?></p>
        </div>
    </div>
<?php endif; ?>

<script>
    function closeModal() {
        document.getElementById('errorModal').style.display = 'none';
    }
    <?php if ($error_message != ''): ?>
    document.getElementById('errorModal').style.display = 'block';
    <?php endif; ?>
</script>

</body>
</html>
