<?php
// Initialize the error message variable
$error_message = '';

// Check if there's an error message for login
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'invalid_credentials') {
        $error_message = "Invalid email or password. Please try again.";
    }
    // Add more error cases here as needed
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us</title>
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
    <h1>CONTACT US</h1>
    <p>We’d love to hear from you! Whether you have questions, feedback, or need assistance, feel free to reach out.</p>
    <p><strong>Email:</strong> <a href="mailto:21329354@studentmail.ul.ie">21329354@studentmail.ul.ie</a></p>
    <p><strong>Phone:</strong> <a href="tel:+353833025325">(083) 302-5325</a></p>
    <p>If you'd like to share feedback or require additional information, don't hesitate to get in touch. Your support helps us improve Safe Haven!</p>
    <p class="back-link"><a href="register.php">Back to Registration</a></p>
</div>


<div class="right-section">
    <img src="safe_haven_text.png" alt="Safe Haven Text Logo" class="text-logo">
    <img src="safe_haven_logo.png" alt="Safe Haven Logo" class="logo">
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

<!-- Modal for authentication error -->
<div class="modal" id="authModal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeAuthModal()">&times;</span>
        <h3>Access Restricted</h3>
        <p id="authMessage"></p>
    </div>
</div>

<script>
    // Function to close the error modal
    function closeModal() {
        document.getElementById('errorModal').style.display = 'none';
    }

    // Show the error modal if there's an error message
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
