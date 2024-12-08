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
    <h1>ABOUT US</h1>
    <p>Welcome to <strong>Safe Haven</strong>, a platform dedicated to supporting mental well-being in a modern, accessible way.</p>
    <p>My name is <strong>Fionn O'Gorman</strong>, and I am currently in my 4th year at the <strong>University of Limerick</strong>, pursuing a degree in <strong>Computer Systems</strong>. Safe Haven is being developed as part of my <strong>Final Year Project</strong>.</p>
    <p>This project stems from my passion for using technology to make a positive impact on people's lives. Mental health is an issue that affects so many, yet access to support can often feel out of reach. By creating this website, my goal is to bridge that gap and provide a space where users can find tools and resources to manage their mental well-being.</p>
    <p>Safe Haven offers features like guided mindfulness exercises, mood tracking, and a journal to help users navigate life's challenges. The platform is built with simplicity and accessibility in mind, ensuring anyone can benefit from its tools regardless of their technical background.</p>
    <p>Thank you for taking the time to explore Safe Haven. Your support means the world to me as I strive to create something meaningful and impactful!</p>
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