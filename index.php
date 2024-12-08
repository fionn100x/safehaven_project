<?php
// Initialize the error message variable
$error_message = '';

// Check if there's an error message from register_process.php
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'email_exists') {
        $error_message = "This email is already registered. Please try a different email.";
    } elseif ($_GET['error'] == 'password_capital') {
        $error_message = "Password must contain at least one capital letter.";
    } elseif ($_GET['error'] == 'password_length') {
        $error_message = "Password must be at least 8 characters long.";
    } elseif ($_GET['error'] == 'password_number') {
        $error_message = "Password must contain at least one number.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
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
    <h1>WELCOME TO SAFE HAVEN</h1>
    <p>Your journey to better mental well-being begins here.</p>
    <p>Safe Haven is your one-stop platform for managing and improving mental health.</p>

    <div class="button-container">
        <a href="register.php" class="button">REGISTER</a>
        <a href="login.php" class="button">LOGIN</a>
    </div>
</div>

<div class="right-section">
    <img src="safe_haven_text.png" alt="Safe Haven Text Logo" class="text-logo"> <!-- Text logo at the top -->
    <img src="safe_haven_logo.png" alt="Safe Haven Logo" class="logo"> <!-- Main logo in the center -->

    <div class="welcome-text">
        <h2>Your journey starts here!</h2>
        <p>At Safe Haven, we provide the tools and support to help you manage your mental well-being.</p>
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
