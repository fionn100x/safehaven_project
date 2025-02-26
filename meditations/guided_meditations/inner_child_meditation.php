<?php
// Start the session to access the logged-in user's data
session_start();

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "db";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's ID from the session (assuming it's stored in session data)
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null; // Ensure user ID is defined
$currentLevel = 0; // Default to 0 in case user is not logged in

// Check if the user is logged in and the ID exists
if ($userId) {
    // Fetch the user's current level
    $levelSql = "SELECT level FROM profiles WHERE user_id = ?";
    $levelStmt = $conn->prepare($levelSql);
    $levelStmt->bind_param("i", $userId);
    $levelStmt->execute();
    $levelResult = $levelStmt->get_result();
    $levelRow = $levelResult->fetch_assoc();

    if ($levelRow) {
        $currentLevel = $levelRow['level']; // Set the user's level
    }

    $levelStmt->close();

    // Handle incoming JSON request for modal update
    $data = json_decode(file_get_contents("php://input"), true);

    if (isset($data['modal_shown']) && $data['modal_shown'] === 'true') {
        // Calculate XP gain (halves each level, minimum of 200 XP)
        $xpGain = max(5000 / pow(2, $currentLevel), 200);

        // Adjust Blossoms based on the user's level
        $blossomsGain = ($currentLevel == 0) ? 20 : 5;

        // Update Blossoms by the calculated amount
        $blossomsSql = "UPDATE profiles SET Blossoms = Blossoms + ? WHERE user_id = ?";
        $blossomsStmt = $conn->prepare($blossomsSql);
        $blossomsStmt->bind_param("ii", $blossomsGain, $userId);
        $blossomsStmt->execute();

        // Update XP based on calculated gain
        $xpSql = "UPDATE profiles SET XP = XP + ? WHERE user_id = ?";
        $xpStmt = $conn->prepare($xpSql);
        $xpStmt->bind_param("ii", $xpGain, $userId);
        $xpStmt->execute();

        // Check if updates were successful
        if ($blossomsStmt->affected_rows > 0 && $xpStmt->affected_rows > 0) {
            echo json_encode([
                "status" => "success",
                "message" => "Blossoms and XP updated successfully!",
                "xpGained" => $xpGain,
                "blossomsGained" => $blossomsGain
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update Blossoms or XP."
            ]);
        }

        // Close the statements
        $blossomsStmt->close();
        $xpStmt->close();
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inner Child Meditation</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome CDN -->
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=vDSX2qjz"></script>
</head>
    <style>
        body {
            margin: 0;
            height: 100vh; /* Full screen */
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: black; /* Start with black */
            animation: fadeToImage 5s ease-in-out 8s forwards; /* Background fades after text */
            position: relative;
            text-align: center;
        }

        .text {
            font-size: 4rem; /* Much larger text */
            font-weight: bold;
            color: white;
            font-family: 'Nunito', sans-serif; /* Apply Nunito font */
            opacity: 0; /* Start invisible */
            animation: fadeIn 3s ease-in-out 1s forwards, fadeOut 3s ease-in-out 7s forwards;
            position: absolute;
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Adjust for exact center */
        }

        /* Text fades in over 3 seconds */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Text fades out over 3 seconds */
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }

        /* Background fades from black to gradient over 5 seconds */
        @keyframes fadeToImage {
            0% { background-color: black; }
            100% { background: url('../../pictures/inner_child_5.jpg') no-repeat center center / cover; }
        }

        .fade-in-text {
            opacity: 0;
            animation: fadeInText 2s ease-in-out forwards, fadeOutText 2s ease-in-out 5s forwards;
            font-size: 1.5rem;
            color: white;
            font-family: 'Nunito', sans-serif;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        /* Add styles for controls */
        .controls {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .start-button {
            padding: 10px 20px;
            background-color: #9b59b6; /* Purple */
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        .volume-control {
            width: 200px;
        }

        /* Fade-in style for the start button */
        .start-button {
            opacity: 0; /* Start invisible */
            animation: fadeInStartButton 3s ease-in-out 12s forwards; /* Fade in after 12 seconds */
            padding: 15px 30px;
            background-color: #9b59b6; /* Purple */
            color: white;
            font-size: 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Fade-in style for the description above the start button */
        .description {
            opacity: 0; /* Start invisible */
            animation: fadeInDescription 3s ease-in-out 12s forwards; /* Fade in at the same time as the start button */
            font-size: 1.25rem;
            color: black; /* Black text color */
            margin-bottom: 20px;
            font-family: 'Nunito', sans-serif; /* Apply Nunito font */
        }

        /* Animation for fade-in of the start button */
        @keyframes fadeInStartButton {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Animation for fade-in of the description */
        @keyframes fadeInDescription {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .logo {
            position: absolute;
            top: 10px; /* Distance from top */
            left: 10px; /* Distance from left */
            max-width: 150px; /* Set the max width of the logo */
        }

        /* Intro image styling */
        .intro-image {
            max-width: 300px;  /* Adjust the width to a smaller size */
            height: auto;      /* Maintain aspect ratio */
            margin-bottom: 20px; /* Space between image and description */
            border: 10px solid #7a5a3e;  /* Brown border to resemble a photo frame */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);  /* Soft shadow for depth */
            border-radius: 10px;  /* Rounded corners */
        }

        .fade-out {
            animation: fadeOut 2s ease-out forwards; /* Apply fadeOut animation */
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }

        .icon-container {
            display: inline-block; /* Ensures it takes up only the space of the icon */
            cursor: pointer; /* Makes it obvious that it's clickable */
            padding: 10px; /* Adds space around the icon for easier clicking */
            position: relative; /* Keeps it contained and in the flow */
        }

        #speakerIcon {
            font-size: 2rem; /* Icon size */
            color: white;
        }

        .image-container img {
            opacity: 0;
            transition: opacity 2s;
        }

        @keyframes fadeInProgress {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        #progressContainer {
            opacity: 0;  /* Start fully invisible */
            transition: opacity 1s ease-in-out; /* Smooth fade-in over 1s */
        }

        #countdownContainer {
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            color: white;
            background: rgba(0, 0, 0, 0.6);
            padding: 10px;
            border-radius: 10px;
            width: 120px;
            margin: 20px auto;
            position: absolute;
            top: 10%; /* Move the countdown above everything */
            left: 50%;
            transform: translateX(-50%);
            z-index: 10; /* Ensure it stays on top */
            transition: opacity 1s ease-in-out;
        }

        #completionModal {
            display: none; /* Modal is hidden by default */
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(200, 160, 255, 0.8); /* Light purple background with transparency */
            justify-content: center;
            align-items: center;
            z-index: 9999; /* Make sure modal is on top */
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        #completionModal .modal-content {
            background: linear-gradient(to bottom, #D8D8D8, #C8A2D9); /* Light silver to light purple gradient */
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            border: 3px solid #4B0082; /* Dark purple outline */
        }

        #completionModal h2 {
            font-size: 24px;
            margin-bottom: 15px;
            color: white; /* Main text white */
        }

        #completionModal p {
            font-size: 18px;
            margin-bottom: 20px;
            color: white; /* Main text white */
        }

        #completionModal img {
            width: 50px;
            height: 50px;
            margin-bottom: 20px;
        }

        #completionModal button {
            background-color: white;
            color: black;
            padding: 10px 20px;
            border: 2px solid #4B0082; /* Dark purple border */
            border-radius: 5px;
            margin: 5px;
            cursor: pointer;
            font-size: 16px;
            font-family: 'Nunito', sans-serif;
        }

        #completionModal button:hover {
            background-color: #f0f0f0;
        }

        #completionModal .blossoms-number {
            color: red;
        }

        .xp-number {
            color: green;
        }

        /* Modal visibility */
        #completionModal.show {
            display: flex; /* Show modal when .show class is added */
            opacity: 1; /* Make it fully visible */
        }

        /* Animation for the modal */
        @keyframes bounce {
            0% { transform: scale(1); }
            30% { transform: scale(1.1); }
            50% { transform: scale(1); }
            70% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Applying the bounce animation */
        #completionModal.show .modal-content {
            animation: bounce 1s ease;
        }
    </style>
</head>
<body>

<img src="../../safe_haven_logo.png" alt="Safe Haven Logo" class="logo">

<div class="text">Inner Child Meditation</div>

<div id="meditationContent"></div>

<!-- Audio Element -->
<audio id="meditationMusic" src="../../audio/inner_child_meditation_audio.mp3" preload="auto" loop></audio>

<!-- Dialog -->
<audio id="meditationDialog" src="../../audio/inner_child_meditation.mp3" preload="auto"></audio>

<div id="imageContainer" class="image-container" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; z-index:1000;">
    <img id="innerChildImage" alt="Inner Child Image" style="width:100%; height:100%; object-fit:cover; opacity: 0; transition: opacity 2s;">
</div>

<!-- Controls -->
<div class="controls">
    <!-- Using a div with padding to ensure the clickable area is only around the icon -->
    <div class="icon-container" id="musicToggle">
        <i class="fas fa-volume-up" id="speakerIcon"></i> <!-- Default speaker icon -->
    </div>
    <input type="range" id="volumeControl" class="volume-control" min="0" max="1" step="0.01" value="0.5">
</div>

<script src="https://code.responsivevoice.org/responsivevoice.js?key=vDSX2qjz"></script>

<div class="description">
    <img src="../../pictures/inner_child_intro.jpg" alt="Inner Child Meditation" class="intro-image">
    <p></p>
    <p></p>
    <button class="start-button" id="startButton" style="font-family: 'Nunito', sans-serif; background-color: black">Start Meditation</button>
    <button id="pauseButton" style="display: none; opacity: 0; position: fixed; left: 50%; top: 50%; transform: translate(-50%, -50%); background: black; border: none; padding: 20px; border-radius: 10px; cursor: pointer; transition: opacity 0.5s;">
        <i id="pauseIcon" class="fas fa-pause" style="font-size: 40px; color: white;"></i>
    </button>
    <div id="progressContainer" style="display: none; position: fixed; left: 50%; top: 55%; transform: translateX(-50%); width: 80%; height: 50px; background-color: #ddd; border-radius: 5px; cursor: pointer;">
        <div id="progressBar" style="height: 100%; background-color: black; width: 0%; border-radius: 5px; transition: width 0.2s ease-in-out;"></div>
    </div>
    <div id="countdownContainer">
        <span id="countdownTimer">03:22</span>
    </div>
    <div id="completionModal" class="modal">
        <div class="modal-content">
            <h2>Congratulations!</h2>
            <h3>You’ve earned <strong>
                <span class="blossoms-number">
                    <?php echo ($currentLevel == 0) ? 20 : 5; ?>
                </span>
                </strong> Blossoms.</h3>
            <h3>You’ve also gained <strong>
            <span class="xp-number">
                <?php echo max(5000 / pow(2, $currentLevel), 200); ?>
            </span>
                </strong> XP.</h3>
            <img src="../../pictures/blossoms_icon.png" alt="Blossoms Icon">
            <div>
                <button id="restartMeditation">Restart Meditation</button>
                <button id="goToGuidedMeditations">Go to Guided Meditations</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        let music = document.getElementById("meditationMusic");  // Music file
        let dialog = document.getElementById("meditationDialog");  // Dialog file
        let musicToggleButton = document.getElementById("musicToggle");
        let speakerIcon = document.getElementById("speakerIcon");
        let volumeControl = document.getElementById("volumeControl");
        let startButton = document.getElementById("startButton");

        const imageContainer = document.getElementById("imageContainer");
        const innerChildImage = document.getElementById("innerChildImage");
        const pauseButton = document.getElementById("pauseButton");
        const pauseIcon = document.getElementById("pauseIcon");

        const progressBar = document.getElementById("progressBar");
        const progressContainer = document.getElementById("progressContainer");

        let isPaused = false; // Track pause/play state
        let progressStartTime = 0; // To store the starting time for progress tracking
        let elapsedTimeBeforePause = 0;

        let isSeeking = false; // Flag for seeking
        let isComplete = false; // Flag for completion

        let remainingTime = 0; // Global variable for remaining time
        let timerInterval; // Global variable for countdown interval

        // Default dialog volume
        const dialogVolume = 1.0;
        dialog.volume = dialogVolume;

        // Event listener for volume control change
        volumeControl.addEventListener('input', function () {
            music.volume = volumeControl.value;
        });

        // Music toggle button (Speaker icon change)
        musicToggleButton.addEventListener('click', function () {
            if (music.paused) {
                music.play();
                speakerIcon.classList.remove('fa-volume-mute');
                speakerIcon.classList.add('fa-volume-up');
            } else {
                music.pause();
                speakerIcon.classList.remove('fa-volume-up');
                speakerIcon.classList.add('fa-volume-mute');
            }
        });

        startButton.addEventListener("click", function () {
            startCountdown(202);
        });

        // Start meditation button
        startButton.addEventListener("click", function () {
            // Fade out intro elements
            const introImage = document.querySelector(".intro-image");
            const textElements = document.querySelectorAll(".text, .description p");

            progressContainer.style.display = "block";

            introImage.classList.add("fade-out");
            textElements.forEach((el) => el.classList.add("fade-out"));
            startButton.classList.add("fade-out");

            setTimeout(() => {
                dialog.play().catch(error => console.log("Dialog playback failed:", error));
                animateProgressBar(dialog.duration);
            }, 2000); // 2-second delay before playing the dialog

            pauseButton.style.display = "block";
            setTimeout(() => {
                pauseButton.style.opacity = 1;
            }, 1000);

            setTimeout(showProgressBar, 1000);

            // Hide elements after fade-out animation
            setTimeout(() => {
                introImage.style.display = "none";
                textElements.forEach((el) => el.style.display = "none");
                startButton.style.display = "none";
            }, 2000);

            // Play background music immediately
            music.play().catch(error => console.log("Music playback failed:", error));

            // Play the dialog after 2 seconds
            setTimeout(() => {
                dialog.play().catch(error => console.log("Dialog playback failed:", error));
            }, 2000);

            // Pause button functionality
            pauseButton.addEventListener("click", function () {
                if (isPaused) {
                    // Resume both music and dialog
                    dialog.play();
                    music.play();
                    isPaused = false;
                    animateProgressBar(dialog.duration);  // Restart progress bar animation from where it left off
                    pauseButton.innerHTML = '<i class="fas fa-pause" style="font-size: 40px; color: white;"></i>'; // Pause icon
                    timerInterval = setInterval(updateCountdown, 1000);
                } else {
                    // Pause both music and dialog
                    dialog.pause();
                    music.pause();
                    isPaused = true;
                    elapsedTimeBeforePause = Date.now() - progressStartTime; // Store the time elapsed before pause
                    pauseButton.innerHTML = '<i class="fas fa-play" style="font-size: 40px; color: white;"></i>'; // Play icon
                    clearInterval(timerInterval);
                }
            });

            // When dialog ends, show the image sequence
            dialog.onended = function() {
                showImageSequence();
                // Ensure countdown and progress bar end at 00:00
                progressBar.style.width = "100%";
                countdownDisplay.innerHTML = "00:00";
            };
        });

        // Countdown timer
        function startCountdown(seconds) {
            const countdownDisplay = document.getElementById("countdownTimer");
            const countdownContainer = document.getElementById("countdownContainer");

            // Show and fade in the countdown container
            countdownContainer.style.display = "block";
            countdownContainer.style.opacity = 1;

            remainingTime = seconds; // Assign to the global variable

            setTimeout(() => {
                countdownContainer.style.opacity = 1;
            }, 10);

            // Clear any existing countdown before starting a new one
            clearInterval(timerInterval);
            updateCountdown(); // Call the global function
            timerInterval = setInterval(updateCountdown, 1000);
        }

        // Update countdown every second
        function updateCountdown() {
            const countdownDisplay = document.getElementById("countdownTimer");

            if (!isPaused) {
                const minutes = Math.floor(remainingTime / 60);
                const seconds = remainingTime % 60;
                countdownDisplay.innerHTML = `${formatTime(minutes)}:${formatTime(Math.floor(seconds))}`;

                if (remainingTime > 0) {
                    remainingTime--;
                } else {
                    clearInterval(timerInterval);
                    countdownDisplay.innerHTML = "00:00"; // Ensure countdown ends at 00:00
                }
            }
        }

        // Format time (keep two digits)
        function formatTime(time) {
            return time < 10 ? `0${time}` : time;
        }

        // Progress bar animation
        function animateProgressBar(duration) {
            progressStartTime = Date.now() - (dialog.currentTime * 1000); // Adjust based on seek position

            function updateProgress() {
                if (isPaused || isSeeking) return;  // Don't update if paused or seeking

                let elapsedTime = Date.now() - progressStartTime;
                let progress = (elapsedTime / (duration * 1000)) * 100;  // Duration is in seconds

                if (progress < 100) {
                    progressBar.style.width = progress + "%"; // Update width of the progress bar
                    requestAnimationFrame(updateProgress); // Continue updating
                } else {
                    progressBar.style.width = "100%"; // Ensure it reaches 100% at the end
                    isComplete = true; // Stop further progress updates
                }
            }

            requestAnimationFrame(updateProgress);
        }

        // Show progress bar
        function showProgressBar() {
            progressContainer.style.display = "block";
            setTimeout(() => {
                progressContainer.style.opacity = "1";
            }, 10); // Small delay to allow the browser to register the change
        }

        // Event listener for progress container click to seek
        progressContainer.addEventListener("click", function (event) {
            if (isComplete) return; // Prevent seeking after completion

            isSeeking = true; // Start seeking

            const rect = progressContainer.getBoundingClientRect();
            const clickX = event.clientX - rect.left;
            const width = progressContainer.clientWidth;

            const percentage = Math.min(Math.max(clickX / width, 0), 1);

            // Seek to the new position in the audio
            dialog.currentTime = percentage * dialog.duration;

            // Instantly update progress bar without animation delay
            progressBar.style.transition = "none"; // Disable transition effect
            progressBar.style.width = `${percentage * 100}%`;

            // Update remaining time immediately
            remainingTime = dialog.duration - dialog.currentTime;
            updateCountdown();

            // Reset progress tracking to the new position
            progressStartTime = Date.now() - (dialog.currentTime * 1000); // Sync start time with seek

            isSeeking = false; // Allow normal progress updates again

            // Restart progress animation from the seeked position
            if (!isPaused) {
                requestAnimationFrame(() => animateProgressBar(dialog.duration));
            }
        });

        // Ensure progress bar stays locked when audio ends
        dialog.addEventListener("ended", function () {
            progressBar.style.width = "100%";
            isComplete = true; // Prevent further changes
            countdownDisplay.innerHTML = "00:00"; // Ensure countdown ends at 00:00
        });

    });
</script>

<script>
    document.getElementById("startButton").addEventListener("click", function () {
        setTimeout(() => {
            let audio = new Audio("../../inner_child_meditation.mp3");
            audio.play();
        }, 2000); // 2-second delay before playing
    });
</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Get references to the modal and buttons
        const completionModal = document.getElementById("completionModal");
        const restartButton = document.getElementById("restartMeditation");
        const guidedMeditationsButton = document.getElementById("goToGuidedMeditations");
        const meditationDialog = document.getElementById("meditationDialog");  // Assuming you have the dialog element

        // Create a new audio element for the sound
        const completedSound = new Audio('../../audio/completed_sound.mp3');  // Replace with the correct path to your sound file

        // Show the modal when the meditation is complete
        function showCompletionModal() {
            completionModal.classList.add("show"); // Show modal with bounce effect
            completionModal.classList.add("bounce");
            completedSound.play();  // Play the completion sound
            updateBlossomsAndXP();
        }

        function updateBlossomsAndXP() {
            fetch('inner_child_meditation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ modal_shown: 'true' })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        console.log("Blossoms and XP updated successfully!");
                    } else {
                        console.log("Error updating Blossoms or XP: ", data.message);
                    }
                })
                .catch(error => console.error("Error with AJAX request: ", error));
        }

        // Restart meditation on button click
        restartButton.addEventListener("click", function () {
            window.location.reload();  // Reload the page to restart meditation
        });

        // Go to Guided Meditations page
        guidedMeditationsButton.addEventListener("click", function () {
            window.location.href = "../../guided_meditation.php";  // Navigate to the guided meditations page
        });

        // Show the modal when the meditation dialog ends
        meditationDialog.addEventListener("ended", showCompletionModal);  // Trigger modal on meditation end
    });
</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const completionModal = document.getElementById("completionModal");

        // Show the modal when the meditation is complete
        function showCompletionModal() {
            completionModal.classList.add("show");

            // Send a POST request to update blossoms when the modal is shown
            fetch('inner_child_meditation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'modal_shown=true' // This signals that the modal has been shown
            });
        }

        // Show the modal when the meditation dialog ends
        meditationDialog.addEventListener("ended", showCompletionModal);
    });
</script>
</body>
</html>
