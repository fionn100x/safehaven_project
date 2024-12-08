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
            <a href="#" onclick="checkAuthentication('home.php')">Home</a>
            <a href="about.php">About Us</a>
            <a href="#" onclick="checkAuthentication('features.php')">Features</a>
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

<?php if ($error_message != ''): ?>
    <div class="modal" id="errorModal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3>Error</h3>
            <p><?php echo $error_message; ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="modal" id="authModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAuthModal()">&times;</span>
        <h3>Access Restricted</h3>
        <p id="authMessage"></p>
    </div>
</div>

<script>
    // Function to close the modal
    function closeModal() {
        document.getElementById('errorModal').style.display = 'none';
    }

    // Show the modal if there's an error message
    <?php if ($error_message != ''): ?>
    document.getElementById('errorModal').style.display = 'block';
    <?php endif; ?>

    // Function to check if a user is authenticated
    function checkAuthentication(page) {
        // Display the appropriate message based on the link clicked
        const authModal = document.getElementById('authModal');
        const authMessage = document.getElementById('authMessage');
        if (page === 'dashboard.php') {
            authMessage.textContent = "You must log in or register to view your Dashboard.";
        } else if (page === 'profile.php') {
            authMessage.textContent = "You must log in or register to create or modify profile settings.";
        } else if (page === 'home.php') {
            authMessage.textContent = "You must log in or register to view your Home page.";
        } else if (page === 'features.php') {
            authMessage.textContent = "You must log in or register to view Features.";
        }

        // Show the modal
        authModal.style.display = 'block';
    }

    // Function to close the authentication modal
    function closeAuthModal() {
        document.getElementById('authModal').style.display = 'none';
    }
</script>

</body>
</html>
