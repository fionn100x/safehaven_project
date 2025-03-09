<?php
session_start();


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
$sql = "SELECT first_name, last_name, birthday, bio, likes, dislikes, friends, meditations, journals, blossoms, level, profile_pic, XP FROM user_profiles WHERE user_id = '$user_id'";
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
$xp_count = $row['XP'];
if ($xp_count >= 10000) {
    // Calculate the remaining XP after leveling up
    $remaining_xp = $xp_count - 10000;

    // Increase level by 1
    $new_level = $level_count + 1;

    // Update the database with the remaining XP for the next level
    $update_level_sql = "UPDATE user_profiles SET XP = ?, level = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_level_sql);
    $stmt->bind_param("iii", $remaining_xp, $new_level, $user_id);

    if ($stmt->execute()) {
        // Update the variables for immediate display
        $xp_count = $remaining_xp;
        $level_count = $new_level;

        // Set session flag for level-up modal
        $_SESSION['level_up'] = true;
    }

    $stmt->close();
}

$meditationPages = [
    'inner_child_meditation.php',
    'selfconfidence_boost.php',
    'letting_go_of_negativity.php',
    'energy_cleansing_meditation.php',
    'compassion_meditation.php',
    'pain_relief_meditation.php',
    'grounding_meditation.php',
    'selflove_meditation.php',
    'overcoming_fear_meditation.php',
    'mindful_presence_meditation.php'
];

$currentPage = basename($_SERVER['PHP_SELF']); // Get current page filename

if (isset($_SESSION['level_up']) && !in_array($currentPage, $meditationPages)) {
    $showLevelUpModal = true;
    unset($_SESSION['level_up']); // Remove flag after displaying modal
} else {
    $showLevelUpModal = false;
}
$profile_pic = $row['profile_pic'] ?: '../pictures/no_profile.jpg'; // Fallback to default if profile picture is not set

// Handle profile updates
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['saveProfile'])) {
    var_dump($_POST); exit;
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $bio = $conn->real_escape_string($_POST['bio']);
    $likes = $conn->real_escape_string($_POST['likes']);
    $dislikes = $conn->real_escape_string($_POST['dislikes']);

    $update_sql = "UPDATE user_profiles SET 
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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['quiz_result'])) {
    $quiz_id = $conn->real_escape_string($_POST['quiz_id']);
    $result = $conn->real_escape_string($_POST['result']);
    $timestamp = date("Y-m-d H:i:s"); // Get current timestamp

    // Insert the quiz result into the user_quizzes table
    $insert_sql = "INSERT INTO user_quizzes (user_id, quiz_id, result, result_date) 
                   VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("iiss", $user_id, $quiz_id, $result, $timestamp);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Quiz result saved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save quiz result']);
    }

    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>16 Personalities</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background: url('../../pictures/personality_quizzes_background.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
        }

        .page-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .content {
            text-align: center;
            opacity: 0;
            transform: translateY(50px);
            animation: fadeIn 3s ease-out forwards;
            z-index: 1;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(50px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                pointer-events: none;
            }
        }

        h1 {
            font-size: 3rem;
            color: #3E2723;
            margin-bottom: 20px;
        }

        p {
            font-size: 1.6rem;
            color: #555;
            max-width: 800px;
            margin: 0 auto;
        }

        .logo {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 150px;
            z-index: 10;
        }

        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #FF7F50;
            color: white;
            font-size: 1.2rem;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s ease, transform 0.2s ease;
            z-index: 1000;
        }

        .back-button:hover {
            background-color: #FF6347;
            transform: scale(1.1);
        }


        .quiz-container {
            display: none; /* Initially hide all quiz containers */
            text-align: center;
            opacity: 0;
            transform: translateY(50px);
            animation: fadeIn 2s ease-out forwards;
            position: relative; /* Position for next arrow placement */
        }



        .quiz-box {
            background-color: #FF9800; /* Orange background */
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: center;
            width: 90%;
            max-width: 500px;
            position: relative;
            opacity: 1;
            transition: opacity 1s ease; /* Fade transition */
        }

        .quiz-box {
            display: none; /* Hide all quiz boxes initially */
        }

        .quiz-box:first-of-type {
            display: block; /* Show the first quiz box */
        }


        .question-number {
            position: absolute;
            top: -15px;
            left: -15px;
            width: 50px;
            height: 50px;
            background-color: white;
            color: red;
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 3px solid red;
        }

        .quiz-question {
            font-size: 1.5rem;
            color: white;
            margin-bottom: 20px;
        }

        .options-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: white;
            border: 2px solid gray;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
            color: white; /* Default color for unselected options */
            position: relative;
        }

        .option:hover {
            transform: scale(1.1);
        }

        .option.selected {
            background-color: lightblue; /* Light blue background on selection */
            color: blue; /* Change font color when selected */
        }

        /* When tick is displayed */
        .option.selected .tick {
            display: block; /* Show the tick */
        }


        .option.selected .tick {
            display: block; /* Show tick when option is selected */
        }

        .tick {
            display: none;
        }

        /* Different colors for options */
        .option[data-value="7"] { background-color: #4CAF50; } /* Strongly Agree */
        .option[data-value="6"] { background-color: #66BB6A; }
        .option[data-value="5"] { background-color: #81C784; }
        .option[data-value="4"] { background-color: #FFD54F; } /* Neutral */
        .option[data-value="3"] { background-color: #FF8A65; }
        .option[data-value="2"] { background-color: #F4511E; }
        .option[data-value="1"] { background-color: #D32F2F; } /* Strongly Disagree */

        .legend {
            position: fixed;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.9);
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 200px;
        }

        .legend h3 {
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.2rem;
            color: #333;
        }

        .legend-item {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }

        .legend-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .strongly-agree { background-color: #4CAF50; }
        .agree { background-color: #66BB6A; }
        .slightly-agree { background-color: #81C784; }
        .neutral { background-color: #FFD54F; }
        .slightly-disagree { background-color: #FF8A65; }
        .disagree { background-color: #F4511E; }
        .strongly-disagree { background-color: #D32F2F; }

        .start-button {
            font-size: 1.5rem;
            padding: 15px 30px;
            background-color: #FF7F50;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 1s ease, transform 1s ease;
            margin-top: 20px;
        }

        .start-button.show {
            opacity: 1;
            transform: translateY(0);
        }
        .next-arrow {
            position: absolute;
            top: 50%;
            right: 20px; /* 20px from the right edge of the quiz container */
            transform: translateY(-50%); /* Center the arrow vertically */
            width: 80px; /* Adjust the size of the arrow */
            height: auto;
            display: none; /* Initially hidden */
            transition: transform 0.2s ease, filter 0.3s ease;
        }

        .next-arrow:hover {
            transform: translateY(-50%) scale(1.2); /* Increase size on hover */
        }

        #congrats-message {
            font-size: 1.8rem;
            color: #3E2723;
            margin-bottom: 20px;
            text-align: center;
            opacity: 0;
            transform: translateY(50px);
            animation: fadeIn 2s ease-out forwards; /* Animation for fading in */
            display: none; /* Make sure it's hidden initially */
        }

        #finish-button {
            font-size: 1.6rem; /* Slightly larger font */
            padding: 20px 40px; /* More padding for a bigger, more clickable button */
            background: linear-gradient(45deg, #FF7F50, #FF6347); /* Gradient background */
            color: white;
            border: none;
            border-radius: 50px; /* Fully rounded edges for a pill-shaped button */
            cursor: pointer;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.3); /* Stronger, more prominent shadow */
            transform: translateY(20px);
            transition: opacity 1s ease, transform 1s ease, box-shadow 0.3s ease, background 0.3s ease;
            display: none; /* Hidden initially */
            margin: 0 auto;
            max-width: 350px; /* Max width for better appearance */
            text-transform: uppercase; /* Uppercase text for a more bold look */
            font-weight: bold; /* Make the text stand out */
            letter-spacing: 1px; /* Spaced out letters for a modern look */
        }

        /* Hover effect */
        #finish-button:hover {
            background: linear-gradient(45deg, #FF6347, #FF7F50); /* Reverse gradient on hover */
            transform: scale(1.1); /* Slight scale-up effect */
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.4); /* Enhanced shadow on hover */
        }

        /* Focus effect for accessibility */
        #finish-button:focus {
            outline: none; /* Remove default focus outline */
            box-shadow: 0 0 0 4px rgba(255, 127, 80, 0.5); /* Subtle focus ring */
        }

        /* Fix fadeIn animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


    </style>
</head>
<body>
<div class="page-container">
    <a href="../../interactive_quizzes.php" class="back-button">Back to Interactive Quizzes</a>
    <img src="../../safe_haven_logo.png" alt="Safe Haven Logo" class="logo">

    <div class="legend">
        <h3>Response Key</h3>
        <div class="legend-item"><div class="legend-circle strongly-agree"></div> Strongly Agree</div>
        <div class="legend-item"><div class="legend-circle agree"></div> Agree</div>
        <div class="legend-item"><div class="legend-circle slightly-agree"></div> Slightly Agree</div>
        <div class="legend-item"><div class="legend-circle neutral"></div> Neutral</div>
        <div class="legend-item"><div class="legend-circle slightly-disagree"></div> Slightly Disagree</div>
        <div class="legend-item"><div class="legend-circle disagree"></div> Disagree</div>
        <div class="legend-item"><div class="legend-circle strongly-disagree"></div> Strongly Disagree</div>
    </div>

    <div class="content">
        <h1>16 Personalities</h1>
        <p>Offers a comprehensive analysis of your personality type based on the Myers-Briggs Type Indicator.</p>
        <button id="start-button" class="start-button">Click here to start the quiz!</button>
    </div>

    <!-- The quiz question -->
    <div class="quiz-container" id="quiz-container">
        <div class="quiz-box" id="quiz-box-1">
            <div class="question-number">1</div>
            <div class="quiz-question">You regularly make new friends.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-2">
            <div class="question-number">2</div>
            <div class="quiz-question">Complex and novel ideas excite you more than simple and straightforward ones.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-3">
            <div class="question-number">3</div>
            <div class="quiz-question">You usually feel more persuaded by what resonates emotionally with you than by factual arguments.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-4">
            <div class="question-number">4</div>
            <div class="quiz-question">Your living and working spaces are clean and organized.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-5">
            <div class="question-number">5</div>
            <div class="quiz-question">You usually stay calm, even under a lot of pressure.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-6">
            <div class="question-number">6</div>
            <div class="quiz-question">You find the idea of networking or promoting yourself to strangers very daunting.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-7">
            <div class="question-number">7</div>
            <div class="quiz-question">You prioritize and plan tasks effectively, often completing them well before the deadline.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-8">
            <div class="question-number">8</div>
            <div class="quiz-question">People’s stories and emotions speak louder to you than numbers or data.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-9">
            <div class="question-number">9</div>
            <div class="quiz-question">You like to use organizing tools like schedules and lists.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-10">
            <div class="question-number">10</div>
            <div class="quiz-question">Even a small mistake can cause you to doubt your overall abilities and knowledge.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-11">
            <div class="question-number">11</div>
            <div class="quiz-question">You feel comfortable just walking up to someone you find interesting and striking up a conversation.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-12">
            <div class="question-number">12</div>
            <div class="quiz-question">You are not too interested in discussions about various interpretations of creative works.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-13">
            <div class="question-number">13</div>
            <div class="quiz-question">You prioritize facts over people’s feelings when determining a course of action.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-14">
            <div class="question-number">14</div>
            <div class="quiz-question">You often allow the day to unfold without any schedule at all.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-15">
            <div class="question-number">15</div>
            <div class="quiz-question">You rarely worry about whether you make a good impression on people you meet.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-16">
            <div class="question-number">16</div>
            <div class="quiz-question">You enjoy participating in team-based activities.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-17">
            <div class="question-number">17</div>
            <div class="quiz-question">You enjoy experimenting with new and untested approaches.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-18">
            <div class="question-number">18</div>
            <div class="quiz-question">You prioritize being sensitive over being completely honest.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-19">
            <div class="question-number">19</div>
            <div class="quiz-question">You actively seek out new experiences and knowledge areas to explore.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-20">
            <div class="question-number">20</div>
            <div class="quiz-question">You are prone to worrying that things will take a turn for the worse.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-21">
            <div class="question-number">21</div>
            <div class="quiz-question">You enjoy solitary hobbies or activities more than group ones.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-22">
            <div class="question-number">22</div>
            <div class="quiz-question">You cannot imagine yourself writing fictional stories for a living.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-23">
            <div class="question-number">23</div>
            <div class="quiz-question">You favor efficiency in decisions, even if it means disregarding some emotional aspects.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-24">
            <div class="question-number">24</div>
            <div class="quiz-question">You prefer to do your chores before allowing yourself to relax.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-25">
            <div class="question-number">25</div>
            <div class="quiz-question">In disagreements, you prioritize proving your point over preserving the feelings of others.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-26">
            <div class="question-number">26</div>
            <div class="quiz-question">You usually wait for others to introduce themselves first at social gatherings.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-27">
            <div class="question-number">27</div>
            <div class="quiz-question">Your mood can change very quickly.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-28">
            <div class="question-number">28</div>
            <div class="quiz-question">You are not easily swayed by emotional arguments.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-29">
            <div class="question-number">29</div>
            <div class="quiz-question">You often end up doing things at the last possible moment.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-30">
            <div class="question-number">30</div>
            <div class="quiz-question">You enjoy debating ethical dilemmas.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-31">
            <div class="question-number">31</div>
            <div class="quiz-question">You usually prefer to be around others rather than on your own.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-32">
            <div class="question-number">32</div>
            <div class="quiz-question">You become bored or lose interest when the discussion gets highly theoretical.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-33">
            <div class="question-number">33</div>
            <div class="quiz-question">When facts and feelings conflict, you usually find yourself following your heart.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-34">
            <div class="question-number">34</div>
            <div class="quiz-question">You find it challenging to maintain a consistent work or study schedule.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-35">
            <div class="question-number">35</div>
            <div class="quiz-question">You rarely second-guess the choices that you have made.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-36">
            <div class="question-number">36</div>
            <div class="quiz-question">Your friends would describe you as lively and outgoing.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-37">
            <div class="question-number">37</div>
            <div class="quiz-question">You are drawn to various forms of creative expression, such as writing.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-38">
            <div class="question-number">38</div>
            <div class="quiz-question">You usually base your choices on objective facts rather than emotional impressions.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-39">
            <div class="question-number">39</div>
            <div class="quiz-question">You like to have a to-do list for each day.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-40">
            <div class="question-number">40</div>
            <div class="quiz-question">You rarely feel insecure.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-41">
            <div class="question-number">41</div>
            <div class="quiz-question">You avoid making phone calls.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-42">
            <div class="question-number">42</div>
            <div class="quiz-question">You enjoy exploring unfamiliar ideas and viewpoints.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-43">
            <div class="question-number">43</div>
            <div class="quiz-question">You can easily connect with people you have just met.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-44">
            <div class="question-number">44</div>
            <div class="quiz-question">If your plans are interrupted, your top priority is to get back on track as soon as possible.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-45">
            <div class="question-number">45</div>
            <div class="quiz-question">You are still bothered by mistakes that you made a long time ago.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-46">
            <div class="question-number">46</div>
            <div class="quiz-question">You are not too interested in discussing theories on what the world could look like in the future.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-47">
            <div class="question-number">47</div>
            <div class="quiz-question">Your emotions control you more than you control them.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-48">
            <div class="question-number">48</div>
            <div class="quiz-question">When making decisions, you focus more on how the affected people might feel than on what is most logical or efficient.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-49">
            <div class="question-number">49</div>
            <div class="quiz-question">Your personal work style is closer to spontaneous bursts of energy than organized and consistent efforts.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-50">
            <div class="question-number">50</div>
            <div class="quiz-question">When someone thinks highly of you, you wonder how long it will take them to feel disappointed in you.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-51">
            <div class="question-number">51</div>
            <div class="quiz-question">You would love a job that requires you to work alone most of the time.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-52">
            <div class="question-number">52</div>
            <div class="quiz-question">You believe that pondering abstract philosophical questions is a waste of time.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-53">
            <div class="question-number">53</div>
            <div class="quiz-question">You feel more drawn to busy, bustling atmospheres than to quiet, intimate places.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-54">
            <div class="question-number">54</div>
            <div class="quiz-question">If a decision feels right to you, you often act on it without needing further proof.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-55">
            <div class="question-number">55</div>
            <div class="quiz-question">You often feel overwhelmed.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-56">
            <div class="question-number">56</div>
            <div class="quiz-question">You complete things methodically without skipping over any steps.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-57">
            <div class="question-number">57</div>
            <div class="quiz-question">You prefer tasks that require you to come up with creative solutions rather than follow concrete steps.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-58">
            <div class="question-number">58</div>
            <div class="quiz-question">You are more likely to rely on emotional intuition than logical reasoning when making a choice.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-59">
            <div class="question-number">59</div>
            <div class="quiz-question">You struggle with deadlines.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
        <div class="quiz-box" id="quiz-box-60">
            <div class="question-number">60</div>
            <div class="quiz-question">You feel confident that things will work out for you.</div>
            <div class="options-container">
                <div class="option" data-value="7">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="6">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="5">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="4">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="3">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="2">
                    <div class="tick">&#10003;</div>
                </div>
                <div class="option" data-value="1">
                    <div class="tick">&#10003;</div>
                </div>
            </div>
        </div>
    </div>

    <img src="../../pictures/next_arrow.png" alt="Next Arrow" id="next-arrow" class="next-arrow" />

    <div id="congrats-message" style="display: none;">
        <h2>Congrats! You've completed the 16 Personalities Quiz!</h2>
    </div>

    <button id="finish-button" style="display: none;">See my personality!</button>


</div>

<script>
    $(document).ready(function() {
        let firstClick = false; // Ensure firstClick is set to false for normal flow

        // Fade in the content and start button when the page loads
        $('.content').css('opacity', 0).animate({ opacity: 1 }, 3000);
        $('#start-button').addClass('show');

        // Initially, hide all quiz boxes except the first one
        $('.quiz-box').hide();
        $('.quiz-box:first').show();

        // When the start button is clicked
        $('#start-button').on('click', function() {
            $('.content').fadeOut(1000);
            $('#start-button').fadeOut(1000);

            setTimeout(function() {
                $('#quiz-container').fadeIn(2000);
            }, 1000);
        });

        // When the next arrow is clicked
        $('#next-arrow').on('click', function() {
            let currentQuizBox = $('.quiz-box:visible');
            let nextQuizBox;

            if (firstClick) {
                // Skip to question 60 on the first click if you're still using that test phase
                nextQuizBox = $('#quiz-box-60');
                firstClick = false; // Reset so the quiz progresses normally after this
            } else {
                // Normal behavior: move to the next question
                nextQuizBox = currentQuizBox.next('.quiz-box');
            }

            // Hide current question and show next
            currentQuizBox.fadeOut(500, function() {
                if (nextQuizBox.length) {
                    nextQuizBox.fadeIn(1000);
                } else {
                    $('#next-arrow').fadeOut(500, function() {
                        $('#congrats-message').fadeIn(1000);
                        $('#finish-button').fadeIn(1000);
                    });
                }
            });

            $('#next-arrow').fadeOut(500);
        });

        // Handle Finish button click
        $('#finish-button').on('click', function() {
            // Calculate the personality type (this could be any result, based on your quiz logic)
            const personalityType = calculatePersonality();  // Replace with your quiz result logic

            // Get the user ID from a global variable or a PHP session value
            const userId = <?php echo $_SESSION['user_id']; ?>; // Make sure this PHP variable is available on the page

            // Get the quiz ID (assuming you have a static quiz ID for this quiz, e.g., 1 for 16Personalities)
            const quizId = 1;  // Change this if you have a dynamic way to determine the quiz ID

            // Prepare data to be sent to the server
            const data = {
                user_id: userId,
                quiz_id: quizId,
                result: personalityType,
                timestamp: new Date().toISOString()  // Generate the timestamp (or let the DB handle it)
            };

            // Send AJAX request to store the result in the database
            $.ajax({
                url: 'store_quiz_result.php',  // The endpoint where the result will be saved
                type: 'POST',
                data: data,
                success: function(response) {
                    // Handle successful response
                    alert('Your result has been saved! Redirecting to results...');
                    window.location.href = "16_personalities_results.php"; // Redirect to the results page
                },
                error: function(xhr, status, error) {
                    // Handle any errors that occur
                    console.error('Error saving quiz result: ', error);
                    alert('There was an error saving your result. Please try again.');
                }
            });
        });
    });

</script>

<script>
    let answers = {
        IvsE: 0,
        SvsN: 0,
        TvsF: 0,
        JvsP: 0
    };

    // Function to track the answers and update the score for each dimension
    function trackAnswer(questionNumber, answerValue) {
        console.log(`Tracking Answer for Question ${questionNumber}: ${answerValue}`);

        // Log the current state of answers to understand the flow
        console.log('Current Answers:', answers);

        // Update the answer for the respective domain based on the question number
        switch (questionNumber) {
            case '1': // Extraversion vs Introversion
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;

            case '2': // Sensing vs Intuition
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                }
                break;

            case '3': // Thinking vs Feeling
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Feeling
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Thinking
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                }
                break;

            case '4': // Judging vs Perceiving
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // Perceiving
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // Judging
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

            case '5': // This is the case for "You usually stay calm, even under a lot of pressure."
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Feeling
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Thinking
                    console.log(`TvsF dEcremented to: ${answers.TvsF}`);
                }
                break;

            // Case 6: You usually stay calm, even under a lot of pressure.
            case '6':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue < 4) {
                    answers.IvsE--; // More Introverted
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE++; // More Extraverted
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                }
                break;

// Case 7: You prioritize and plan tasks effectively, often completing them well before the deadline.
            case '7':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // More Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // More Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

// Case 8: People’s stories and emotions speak louder to you than numbers or data.
            case '8':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue < 4) {
                    answers.SvsN--; // More Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN++; // More Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                }
                break;

// Case 9: You like to use organizing tools like schedules and lists.
            case '9':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // More Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // More Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

// Case 10: Even a small mistake can cause you to doubt your overall abilities and knowledge.
            case '10':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue < 4) {
                    answers.TvsF--; // More Thinking
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF++; // More Feeling
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                }
                break;

// Case 11: You feel comfortable just walking up to someone you find interesting and striking up a conversation.
            case '11':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // More Extraverted
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // More Introverted
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;

// Case 12: You are not too interested in discussions about various interpretations of creative works.
            case '12':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue < 4) {
                    answers.SvsN--; // More Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN++; // More Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                }
                break;

// Case 13: You prioritize facts over people’s feelings when determining a course of action.
            case '13':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue < 4) {
                    answers.TvsF--; // More Thinking
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF++; // More Feeling
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                }
                break;

// Case 14: You often allow the day to unfold without any schedule at all.
            case '14':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue < 4) {
                    answers.JvsP--; // More Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP++; // More Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                }
                break;

// Case 15: You rarely worry about whether you make a good impression on people you meet.
            case '15':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue < 4) {
                    answers.IvsE--; // More Introverted
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE++; // More Extraverted
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                }
                break;

// Case 16: You can easily strike up a conversation with others, even in unfamiliar situations.
            case '16':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;

            case '17':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                }
                break;

// Case 18: You rely on your emotions when making decisions.
            case '18':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Feeling
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Thinking
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                }
                break;

// Case 19: You focus more on ideas and concepts rather than facts and details.
            case '19':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                }
                break;

// Case 20: You prefer making decisions based on structured plans rather than going with the flow.
            case '20':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

// Case 21: You find it easier to recharge and find peace in solitude.
            case '21':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                }
                break;

// Case 22: You value concrete facts more than abstract concepts.
            case '22':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                }
                break;

// Case 23: You make decisions based on logical analysis rather than feelings.
            case '23':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                }
                break;

// Case 24: You tend to make decisions quickly, following your schedule.
            case '24':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

// Case 25: You prefer planning things in advance rather than acting spontaneously.
            case '25':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                }
                break;

// Case 26: You prefer spending time alone or in small, intimate groups.
            case '26':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                }
                break;

// Case 27: You enjoy being spontaneous and adaptable, allowing your plans to change.
            case '27':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                }
                break;

            case '28':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                }
                break;

// Case 29: You prefer flexibility and spontaneity over rigid plans.
            case '29':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                }
                break;

// Case 30: You rely on facts and logic rather than emotions when making decisions.
            case '30':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                }
                break;

// Case 31: You are energized by socializing and interacting with others.
            case '31':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;

// Case 32: You focus on abstract concepts and possibilities rather than details.
            case '32':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                }
                break;

// Case 33: You find it easier to make decisions based on your feelings rather than logic.
            case '33':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Feeling
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Thinking
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                }
                break;

// Case 34: You are flexible and adaptable, preferring to go with the flow rather than stick to a plan.
            case '34':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                }
                break;

// Case 35: You prefer a structured approach and tend to plan things in advance.
            case '35':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

// Case 36: You are more outgoing and enjoy being in social situations.
            case '36':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;

// Case 37: You trust your intuition more than facts and details.
            case '37':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                }
                break;

// Case 38: You tend to focus on logical analysis rather than emotions.
            case '38':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                }
                break;
            // Case 39: You prefer structured, organized environments and work well with clear expectations.
            case '39':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

// Case 40: You feel energized when surrounded by social interactions.
            case '40':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;

// Case 41: You enjoy having time alone to recharge and reflect.
            case '41':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                }
                break;

// Case 42: You prefer exploring new ideas and abstract concepts.
            case '42':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                }
                break;

// Case 43: You thrive in social settings and enjoy meeting new people.
            case '43':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;

// Case 44: You value structure and enjoy making detailed plans.
            case '44':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

// Case 45: You are influenced more by your feelings than by logic when making decisions.
            case '45':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                }
                break;

// Case 46: You prefer relying on your senses to gather concrete information.
            case '46':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                }
                break;

// Case 47: You tend to make decisions based on logic rather than emotions.
            case '47':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                }
                break;

// Case 48: You often prioritize logic and objectivity over emotional considerations.
            case '48':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                }
                break;

// Case 49: You enjoy spontaneity and flexibility, and dislike rigid schedules.
            case '49':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                }
                break;

            // Case 50: You make decisions based more on feelings than on objective logic.
            case '50':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                }
                break;

// Case 51: You prefer solitude and time to yourself rather than socializing.
            case '51':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                }
                break;

// Case 52: You tend to trust concrete data and facts over abstract concepts.
            case '52':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                }
                break;

// Case 53: You enjoy socializing and being around people rather than being alone.
            case '53':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;

// Case 54: You make decisions based on emotions and personal values more than on logic.
            case '54':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                }
                break;

// Case 55: You tend to enjoy time with others but also value your personal space.
            case '55':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                }
                break;

// Case 56: You prefer having a structured environment and working with clear plans.
            case '56':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                }
                break;

// Case 57: You find yourself more energized by ideas and possibilities than by real-world facts.
            case '57':
                console.log(`SvsN Current: ${answers.SvsN}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.SvsN++; // Intuition
                    console.log(`SvsN Incremented to: ${answers.SvsN}`);
                } else {
                    answers.SvsN--; // Sensing
                    console.log(`SvsN Decremented to: ${answers.SvsN}`);
                }
                break;

// Case 58: You prioritize logical thinking over emotional responses in decision-making.
            case '58':
                console.log(`TvsF Current: ${answers.TvsF}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.TvsF--; // Feeling
                    console.log(`TvsF Decremented to: ${answers.TvsF}`);
                } else {
                    answers.TvsF++; // Thinking
                    console.log(`TvsF Incremented to: ${answers.TvsF}`);
                }
                break;

// Case 59: You prefer working in a flexible and spontaneous environment rather than one with strict deadlines.
            case '59':
                console.log(`JvsP Current: ${answers.JvsP}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.JvsP--; // Perceiving
                    console.log(`JvsP Decremented to: ${answers.JvsP}`);
                } else {
                    answers.JvsP++; // Judging
                    console.log(`JvsP Incremented to: ${answers.JvsP}`);
                }
                break;

// Case 60: You enjoy social activities and prefer interacting with others rather than staying by yourself.
            case '60':
                console.log(`IvsE Current: ${answers.IvsE}, Selected Value: ${answerValue}`);
                if (answerValue > 4) {
                    answers.IvsE++; // Extraversion
                    console.log(`IvsE Incremented to: ${answers.IvsE}`);
                } else {
                    answers.IvsE--; // Introversion
                    console.log(`IvsE Decremented to: ${answers.IvsE}`);
                }
                break;


        }

        // Log the updated scores to debug
        console.log('Updated Scores:', answers);
    }

    // Handle option clicks for question 1
    $(document).ready(function() {
        $('.option').on('click', function() {  // Changed .one() to .on()
            // Remove the 'selected' class from all options
            $('.option').removeClass('selected');
            // Add the 'selected' class to the clicked option
            $(this).addClass('selected');

            // Get the question number (e.g., quiz-box-1 means question 1)
            const questionNumber = $(this).closest('.quiz-box').attr('id').split('-')[2]; // Ensure this is correct

            // Debugging: Log the question number
            console.log(`Question ID: ${$(this).closest('.quiz-box').attr('id')}`);
            console.log(`Extracted Question Number: ${questionNumber}`);

            // Get the selected answer value
            const answerValue = parseInt($(this).attr('data-value'));

            // Log the selected answer value
            console.log(`Selected answer value: ${answerValue}`);

            // Track the answer based on question number and answer value
            trackAnswer(questionNumber, answerValue);

            // Debugging: Log the current scores for each dimension
            console.log('Current Scores: ', answers);

            // Show the next arrow when an option is selected
            $('#next-arrow').fadeIn(500);
        });
    });

    // Function to calculate the final personality type based on accumulated scores
    function calculatePersonality() {
        // Determine which side of each dimension the user leans towards
        let IvsE = answers.IvsE > 0 ? 'E' : 'I';
        let SvsN = answers.SvsN > 0 ? 'N' : 'S';
        let TvsF = answers.TvsF > 0 ? 'F' : 'T';
        let JvsP = answers.JvsP > 0 ? 'P' : 'J';

        // Return the combined personality type
        return `${IvsE}${SvsN}${TvsF}${JvsP}`;
    }
</script>


</body>
</html>
