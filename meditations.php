<?php
session_start();

include('header.php');

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


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Query to get user profile details
$sql = "SELECT first_name, last_name, birthday, bio, likes, dislikes, friends, meditations, journals, blossoms, level, profile_pic FROM profiles WHERE user_id = '$user_id'";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// Get the data and calculate the age
$first_name = $row['first_name'];
$last_name = $row['last_name'];
$birthday = new DateTime($row['birthday']);
$age = $birthday->diff(new DateTime())->y; // Calculate age from birthday
$bio = $row['bio'];
$likes = $row['likes'];
$dislikes = $row['dislikes'];
$friends_count = $row['friends'];
$meditations_count = $row['meditations'];
$journals_count = $row['journals'];
$blossoms_count = $row['blossoms'];
$level_count = $row['level'];
$profile_pic = $row['profile_pic'] ?: 'pictures/no_profile.jpg'; // Fallback to default if profile picture is not set

// Handle profile updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['saveProfile'])) {
    var_dump($_POST); exit;
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $bio = $conn->real_escape_string($_POST['bio']);
    $likes = $conn->real_escape_string($_POST['likes']);
    $dislikes = $conn->real_escape_string($_POST['dislikes']);

    $update_sql = "UPDATE profiles SET 
                    first_name = '$firstName', 
                    last_name = '$lastName', 
                    bio = '$bio', 
                    likes = '$likes', 
                    dislikes = '$dislikes' 
                  WHERE user_id = '$user_id'";

    if ($conn->query($update_sql) === TRUE) {
        echo "Update Successful";
        header("Location: dashboard.php"); // Refresh the page to reflect changes
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }
}

// Close the database connection
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="dashboard.css">
    <link rel="stylesheet" type="text/css" href="meditations.css">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <title>Dashboard</title>

</head>
<body>
<audio id="backgroundMusic" loop>
    <source src="audio/background_music_1.mp3" type="audio/mpeg">
    Your browser does not support the audio element.
</audio>

<div class="safe-haven-text">
    <img src="safe_haven_text.png" alt="Safe Haven Text" class="safe-haven-img">
</div>
<div class="profile-info">
    <audio id="backgroundMusic" loop>
        <source src="audio/background_music_1.mp3" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <div class="profile-level">
        <img src="pictures/level_icon.png" alt="Level Icon" class="level-icon">
        <p class="level-count">Level: <?php echo $meditations_count; ?></p>
    </div>
    <div class="profile-blossoms">
        <img src="pictures/blossoms_icon.png" alt="Blossoms Icon" class="blossoms-icon">
        <p class="blossoms-count"><?php echo $meditations_count; ?> Blossoms</p>
    </div>
    <div class="profile-meditations">
        <img src="pictures/meditations_icon.png" alt="Meditations Icon" class="meditations-icon">
        <p class="meditations-count"><?php echo $meditations_count; ?> Meditations</p>
    </div>
    <div class="profile-journals">
        <img src="pictures/journals_icon.png" alt="Journals Icon" class="journals-icon">
        <p class="journals-count"><?php echo $journals_count; ?> Journals</p>
    </div>
    <div class="profile-friends">
        <img src="pictures/friends_icon.png" alt="Friends Icon" class="friends-icon">
        <p class="friends-count"><?php echo $friends_count; ?> Friends</p>
    </div>

    <!-- Profile Circle with correct ID -->
    <div class="profile-circle" id="profileCircle">
        <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="profile-pic">
    </div>
</div>

<div class="spacer"></div>
<div class="sidebar">
    <img src="safe_haven_logo.png" alt="Safe Haven Logo" class="sidebar-logo">
    <div class="sidebar-buttons">
        <a href="meditations.php">Meditations</a>
        <a href="#">Interactive Quizzes</a>
        <a href="#">My Journals</a>
        <a href="#">Resources</a>
        <a href="#">Community Forum</a>
        <a href="#">Friends</a>
        <a href="#">Groups</a>
        <a href="#">Affirmations</a>
    </div>
</div>

<!-- Main Content Section -->
<div class="main-content">
    <div style="min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white;">
        <h1 style="font-size: 3rem; position: relative; top: -140px; right: -110px;">
            Welcome To Meditations!
        </h1>
    </div>
    <header>
        <div id="musicControl">
            <button id="playPauseBtn">
                <i class="fas fa-play"></i> <!-- FontAwesome Play Icon -->
            </button>
        </div>
        <nav>
            <div class="banner">
                <a href="#">Home</a>
                <a href="#">About Us</a>
                <a href="#">Features</a>
                <a href="#">Contact Us</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="#">Profile Settings</a>
            </div>
        </nav>
    </header>
</div>
<div class="meditation-container">
    <a href="meditations/guided_meditation.php" class="meditation-box-link">
        <div class="meditation-box">
            <img src="pictures/guided_meditation.jpg" alt="Guided Meditation" class="box-image">
            <div class="box-content">Guided Meditation</div>
        </div>
    </a>

    <div class="meditation-box">
        <img src="pictures/breathing_exercises.jpg" alt="Breathing Exercises" class="box-image">
        <div class="box-content">
            Breathing Exercises
        </div>
    </div>
    <div class="meditation-box">
        <img src="pictures/body_scan.jpg" alt="Body Scan Meditation" class="box-image">
        <div class="box-content">
            Body Scan Meditation
        </div>
    </div>
    <div class="meditation-box">
        <img src="pictures/gratitude_meditation.jpg" alt="Gratitude Meditation" class="box-image">
        <div class="box-content">
            Gratitude Meditation
        </div>
    </div>
    <div class="meditation-box">
        <img src="pictures/visualisation.jpg" alt="Visualisation" class="box-image">
        <div class="box-content">
            Visualisation
        </div>
    </div>
    <div class="meditation-box">
        <img src="pictures/sleep.jpg" alt="Sleep Meditation" class="box-image">
        <div class="box-content">
            Sleep Meditation
        </div>
    </div>
    <div class="meditation-box">
        <img src="pictures/yoga.jpg" alt="Yoga" class="box-image">
        <div class="box-content">
            Yoga
        </div>
    </div>
</div>



<div class="modal" id="profileModal">
    <div class="modal-content">
        <img src="safe_haven_logo.png" alt="Safe Haven Logo" class="modal-logo">
        <h2>Profile Options</h2>
        <span class="close-btn" id="closeProfileModal">&times;</span>
        <button id="viewProfileBtn">View Profile</button>
        <button id="editProfileBtn">Edit Profile</button>
        <button id="logoutBtn">Logout</button>
    </div>
</div>
<div id="viewProfileModal" class="modal">
    <div class="modal-content">
        <!-- Profile Picture -->
        <img src="<?php echo $profile_pic; ?>" alt="Profile Picture" class="modal-profile-pic" id="modalProfilePic">

        <!-- User Info -->
        <h2 id="profileName" style="color: white;"><?php echo $first_name . ' ' . $last_name . ' (' . $age . ')'; ?></h2>

        <!-- Profile Bio -->
        <p id="profileBio" style="color: black;"><?php echo $bio; ?></p>

        <!-- Stats with Icons: Likes, Dislikes, and Friends in one row -->
        <div class="stats-row">
            <div class="stat-card">
                <i class="fas fa-thumbs-up"></i> <!-- Icon for likes -->
                <span>Likes: <?php echo $likes; ?></span>
            </div>

            <div class="stat-card">
                <i class="fas fa-thumbs-down"></i> <!-- Icon for dislikes -->
                <span>Dislikes: <?php echo $dislikes; ?></span>
            </div>

            <div class="stat-card">
                <i class="fas fa-heart"></i> <!-- Icon for friends -->
                <span>Friends: <?php echo $friends_count; ?></span>
            </div>
        </div>

        <!-- Stats with Icons: Blossoms, Meditations, and Journals in another row -->

        <div class="stat-card">
            <i class="fas fa-leaf"></i> <!-- Icon for blossoms -->
            <span>Blossoms: <?php echo $blossoms_count; ?></span>
        </div>

        <div class="stat-card">
            <i class="fas fa-spa"></i> <!-- Icon for meditations -->
            <span>Meditations: <?php echo $meditations_count; ?></span>
        </div>

        <div class="stat-card">
            <i class="fas fa-journal-whills"></i> <!-- Icon for journals -->
            <span>Journals: <?php echo $journals_count; ?></span>
        </div>


        <!-- Level with Progress Bar -->
        <div class="progress-bar">
            <span>Level: <?php echo $level_count; ?></span>
            <div class="progress">
                <div class="progress-filled" style="width: <?php echo $level_count * 10; ?>%"></div>
            </div>
        </div>

        <!-- Close Button -->
        <span class="close-btn">&times;</span>
    </div>
</div>
<div id="editProfileModal">
    <div class="modal-content">
        <span class="close-btn">&times;</span> <!-- Close button -->
        <h2 id="editProfileName" style="color: white">Edit Profile</h2>

        <form id="editProfileForm" action="dashboard.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

            <div class="profile-picture-container">
                <label for="profilePicInput">
                    <img id="editProfileCircle" class="modal-profile-pic"
                         src="<?php echo $profile_pic; ?>"
                         alt="Profile Picture">
                </label>
                <input type="file" id="profilePicInput" accept="image/*" style="display: none;">
            </div>
            <label for="editFirstName">First Name:</label>
            <input type="text" id="editFirstName" name="first_name" value="<?php echo $row['first_name']; ?>" required>

            <label for="editLastName">Last Name:</label>
            <input type="text" id="editLastName" name="last_name" value="<?php echo $row['last_name']; ?>" required>

            <label for="editBio">Bio:</label>
            <textarea id="editBio" name="bio" rows="4" required><?php echo $row['bio']; ?></textarea>

            <label for="editLikes">Likes:</label>
            <input type="text" id="editLikes" name="likes" value="<?php echo $row['likes']; ?>" required>

            <label for="editDislikes">Dislikes:</label>
            <input type="text" id="editDislikes" name="dislikes" value="<?php echo $row['dislikes']; ?>" required>

            <button type="submit" name="saveProfile">Save</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const audio = document.getElementById("backgroundMusic");

        // Check if the user has already started music in a previous session
        if (localStorage.getItem("musicPlaying") === "true") {
            audio.play();
        }

        // Ensure the music continues playing on all pages
        audio.onplay = function() {
            localStorage.setItem("musicPlaying", "true");
        };

        audio.onpause = function() {
            localStorage.setItem("musicPlaying", "false");
        };

        // Stop music when user logs out
        document.getElementById("logoutBtn").addEventListener("click", function() {
            audio.pause();
            localStorage.setItem("musicPlaying", "false");
        });
    });
</script>

<script>document.addEventListener("DOMContentLoaded", function () {
        const profileCircle = document.getElementById("profileCircle");
        const profileModal = document.getElementById("profileModal");
        const closeProfileModal = document.getElementById("closeProfileModal");

        const viewProfileBtn = document.getElementById("viewProfileBtn");
        const viewProfileModal = document.getElementById("viewProfileModal");
        const closeViewProfileModal = document.querySelector("#viewProfileModal .close-btn"); // Update this line

        // Function to fetch and display user profile data
        function fetchUserProfile() {
            fetch("dashboard.php") // Fetch the user data from dashboard.php
                .then(response => response.json())
                .then(data => {
                    // Update profile picture and other details in the view profile modal
                    document.getElementById("modalProfilePic").src = data.profile_pic || "pictures/no_profile.jpg";
                    document.getElementById("profileName").textContent = `${data.first_name} ${data.last_name} (${data.age})`;
                    document.getElementById("profileBio").textContent = `Bio: ${data.bio}`;
                    document.getElementById("profileLikes").textContent = `Likes: ${data.likes}`;
                    document.getElementById("profileDislikes").textContent = `Dislikes: ${data.dislikes}`;
                    document.getElementById("profileFriends").textContent = `Friends: ${data.friends}`;
                    document.getElementById("profileMeditations").textContent = `Meditations: ${data.meditations}`;
                    document.getElementById("profileJournals").textContent = `Journals: ${data.journals}`;
                    document.getElementById("profileBlossoms").textContent = `Blossoms: ${data.blossoms}`;
                    document.getElementById("profileLevel").textContent = `Level: ${data.level}`;
                })
                .catch(error => {
                    console.error("Error fetching user profile data:", error);
                });
        }

        // When the profile circle is clicked, show the profile modal
        profileCircle.addEventListener("click", function () {
            profileModal.style.display = "flex"; // Show profile modal
        });

        // When the close button is clicked, hide the profile modal
        closeProfileModal.addEventListener("click", function () {
            profileModal.style.display = "none"; // Hide profile modal
        });

        // When the "View Profile" button is clicked, hide profile modal and show view profile modal
        viewProfileBtn.addEventListener("click", function () {
            profileModal.style.display = "none"; // Hide profile modal
            fetchUserProfile(); // Fetch and display user profile data
            viewProfileModal.style.display = "flex"; // Show view profile modal
        });

        // When the close button is clicked in viewProfileModal, hide the view profile modal
        closeViewProfileModal.addEventListener("click", function () {
            viewProfileModal.style.display = "none"; // Hide view profile modal
        });

        // Optionally, close modal when clicking outside of it
        window.addEventListener("click", function (event) {
            if (event.target === profileModal) {
                profileModal.style.display = "none"; // Close profile modal when clicking outside
            }
            if (event.target === viewProfileModal) {
                viewProfileModal.style.display = "none"; // Close view profile modal when clicking outside
            }
        });
    });</script>

<script>
    const viewProfileModal = document.getElementById('viewProfileModal');
    const editProfileModal = document.getElementById('editProfileModal');
    const profileModal = document.getElementById('profileModal'); // Assuming profileModal exists

    // Open the View Profile Modal
    function openViewProfileModal() {
        viewProfileModal.style.display = 'flex';
        editProfileModal.style.display = 'none';  // Hide the edit modal if it's open
        profileModal.style.display = 'none'; // Hide profileModal when opening viewProfileModal
    }

    // Open the Edit Profile Modal when clicking the "Edit Profile" button
    document.getElementById('editProfileBtn').addEventListener('click', function() {
        // Hide other modals
        viewProfileModal.style.display = 'none';
        profileModal.style.display = 'none';

        // Show the editProfileModal
        editProfileModal.style.display = 'flex';
    });

    // Close the Edit Profile Modal when clicking the close button
    document.querySelector('#editProfileModal .close-btn').addEventListener('click', function() {
        editProfileModal.style.display = 'none';
    });

    // Close the View Profile Modal when clicking the close button
    document.querySelector('#viewProfileModal .close-btn').addEventListener('click', function() {
        viewProfileModal.style.display = 'none';
    });

    // Close modals when clicking outside of the modal content
    window.addEventListener('click', function(event) {
        if (event.target === editProfileModal) {
            editProfileModal.style.display = 'none';
        }
        if (event.target === viewProfileModal) {
            viewProfileModal.style.display = 'none';
        }
    });

    // Handle form submission for the edit profile form
    document.getElementById('editProfileForm').addEventListener('submit', function(event) {
        event.preventDefault();

        // Handle form submission logic here
        const formData = new FormData(this);
        console.log('Profile updated with data:', Object.fromEntries(formData));

        // Optionally, close the modal after form submission
        editProfileModal.style.display = 'none';
    });

    // Initially hide both modals (in case they are showing by default)
    viewProfileModal.style.display = 'none';
    editProfileModal.style.display = 'none';
</script>

<script src="audio.js"></script>

<script>
    document.getElementById("logoutBtn").addEventListener("click", function () {
        window.location.href = "logout.php"; // Redirect to logout script
    });
</script>

</body>
</html>