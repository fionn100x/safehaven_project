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
            <a href="index.php">Home</a>
            <a href="about.php">About Us</a>
            <a href="features.php">Features</a>
            <a href="contact.php">Contact Us</a>
            <a href="#" onclick="checkAuthentication('dashboard.php')">Dashboard</a>
            <a href="#" onclick="checkAuthentication('profile.php')">Profile Settings</a>
        </div>
    </nav>
</header>
<div class="left-section">
    <h1>SIGN UP</h1>
    <p>Enter your email and password to register.</p>
    <form action="register_process.php" method="POST">
        <label for="first_name">First Name</label>
        <input type="text" id="first_name" name="first_name" required>

        <label for="last_name">Last Name</label>
        <input type="text" id="last_name" name="last_name" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <div class="checkbox-container">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">I agree to the terms and conditions</label>
        </div>

        <input type="submit" value="SIGN UP">

        <p class="login-link">Already have an account? <a href="login.php">Sign in</a></p>
    </form>
</div>

<div class="right-section">
    <img src="safe_haven_text.png" alt="Safe Haven Text Logo" class="text-logo"> <!-- Text logo at the top -->
    <img src="safe_haven_logo.png" alt="Safe Haven Logo" class="logo"> <!-- Main logo in the center -->

    <div class="welcome-text">
        <h2>Your journey starts here!</h2>
        <p>At Safe Haven, we provide the tools and support to help you manage your mental well-being.</p>
    </div>
</div>

</body>
</html>