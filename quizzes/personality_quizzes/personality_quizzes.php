<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personality Quizzes</title>
    <!-- Add Google Fonts link for Nunito -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: url('../../pictures/personality_quizzes_background.png') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Prevent body scrolling */
            height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Header container */
        .header-container {
            width: 100%;
            text-align: center;
            position: fixed; /* Keep header fixed at the top */
            top: 0;
            left: 0;
            height: 100vh; /* Full screen height */
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 2; /* Ensure header stays above content */
        }

        /* Bounce in animation */
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: translateY(-50px);
            }
            50% {
                opacity: 1;
                transform: translateY(10px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Hide text initially */
        .welcome-text, .subtext {
            opacity: 0;
            text-align: center;
            position: absolute;
            left: 50%;
            transform: translate(-50%, 0); /* Centers text */
            z-index: 2;
            width: 100%;
        }

        /* Animation applied */
        .welcome-text {
            font-size: 2.5rem;
            color: #3E2723;
            animation: bounceIn 1s ease-out 1s forwards;
            top: 10vh; /* Position at the top */
            left: 50%;
            transform: translate(-50%, 0); /* Ensures horizontal centering */
        }

        .subtext {
            font-size: 1.5rem;
            color: black;
            animation: bounceIn 1s ease-out 2s forwards;
            top: 18vh; /* Position below the welcome text */
            left: 50%;
            transform: translate(-50%, 0); /* Ensures horizontal centering */
        }

        /* Quiz container - overlapping the background */
        @keyframes bounceInContainer {
            0% {
                opacity: 0;
                transform: translateY(50px); /* Start from below */
            }
            50% {
                opacity: 1;
                transform: translateY(-20px); /* Bounce above its final position */
            }
            100% {
                opacity: 1;
                transform: translateY(0); /* Final position */
            }
        }


        /* Quiz container - overlapping the background */
        .quiz-container {
            position: absolute;
            top: 40%; /* Starts at 40% of the viewport height */
            transform: translateY(-50%); /* Centers it vertically */
            width: 90%;
            max-width: 1200px;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            padding: 20px;
            box-sizing: border-box;
            z-index: 3;
            max-height: 60vh; /* Limit the height of the quiz container */
            overflow-y: auto; /* Make it scrollable */
            opacity: 0; /* Initially hidden */
            animation: bounceInContainer 1s ease-out 3s forwards; /* Bounce after 3 seconds */
        }

        /* Ensure the final state of the quiz-container is visible and in place */
        .quiz-container {
            animation-fill-mode: forwards; /* Keeps the final state of the animation */
        }


        /* Individual quiz boxes */
        .quiz-box {
            background: linear-gradient(135deg, #f7a7a6, #f3d9b6); /* Gradient for some color pop */
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15); /* Bigger shadow for a 3D effect */
            text-align: center;
            width: 100%;
            max-width: 350px; /* Slightly larger width */
            height: 300px; /* Increased height for a more spacious look */
            margin: 20px auto;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden; /* Prevents overflow of decorative elements */
        }

        .quiz-box:hover {
            transform: translateY(-10px); /* Slight lift effect when hovering */
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2); /* Deeper shadow on hover */
        }

        .quiz-box::before {
            content: "";
            position: absolute;
            top: 10%;
            left: 0;
            right: 0;
            height: 100px;
            background-color: rgba(0, 0, 0, 0.1);
            transform: skewY(-10deg); /* Slanted top edge for a fun, unique look */
            border-radius: 10px 10px 0 0;
        }

        .quiz-box h3 {
            font-size: 1.8rem;
            color: #fff;
            font-weight: 600;
            margin-bottom: 20px;
            text-transform: uppercase; /* Uppercase text for more emphasis */
            letter-spacing: 1px;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.2); /* Shadow for text readability */
        }

        .quiz-box p {
            color: #fff;
            font-size: 1.2rem;
            margin-bottom: 20px;
            line-height: 1.5;
            font-style: italic; /* Italicize for a more stylish touch */
        }

        .go-button {
            background: linear-gradient(45deg, #4CAF50, #81C784); /* Button with gradient */
            color: white;
            font-size: 1.1rem;
            padding: 12px 30px;
            border: none;
            border-radius: 50px; /* Rounded corners for the button */
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .go-button:hover {
            background: linear-gradient(45deg, #66BB6A, #4CAF50); /* Button hover color */
            transform: translateY(-5px); /* Subtle lift when hovering */
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2); /* Deeper shadow on hover */
        }
        .logo {
            position: absolute;
            top: 20px; /* Adjust to your desired distance from the top */
            right: 20px; /* Adjust to your desired distance from the right */
            width: 150px; /* Increased size */
            height: auto; /* Maintain aspect ratio */
            z-index: 10; /* Ensure it's above other content */
        }
        .back-button {
            position: fixed; /* Fixed position so it stays on the screen while scrolling */
            top: 20px; /* Distance from the top */
            left: 20px; /* Distance from the left */
            background-color: #FF7F50; /* Vibrant button color */
            color: white; /* White text */
            font-size: 1.2rem;
            padding: 15px 30px; /* Spacing inside the button */
            border-radius: 25px; /* Rounded corners */
            text-decoration: none; /* Remove underline */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Soft shadow */
            transition: background-color 0.3s ease, transform 0.2s ease; /* Transition for hover effects */
            z-index: 1000; /* Ensure button stays above other content */
        }

        /* Hover effect */
        .back-button:hover {
            background-color: #FF6347; /* Darker orange on hover */
            transform: scale(1.1); /* Slightly enlarge the button */
        }
        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }

        .fade-out {
            animation: fadeOut 3s ease forwards;
            position: absolute; /* Ensure it stays in place during animation */
            top: 40%; /* Make sure it starts at the same position */
            transform: translateY(-50%); /* Keep it centered vertically */
        }
    </style>
</head>
<body>
<a href="../../interactive_quizzes.php" class="back-button">Back to Interactive Quizzes</a>
<img src="../../safe_haven_logo.png" alt="Safe Haven Logo" class="logo">
<header class="header-container">
    <h1 class="welcome-text" style="margin-left: -50%;">Welcome to Personality Quizzes!</h1>
    <p id="subtext" class="subtext" style="margin-left: -50%;">What quiz would you like to do today?</p>
</header>
<div class="quiz-container">
    <div class="quiz-box">
        <h3>16 PERSONALITIES</h3>
        <p>Offers a comprehensive analysis of your personality type based on the Myers-Briggs Type Indicator.</p>
        <button class="go-button" onclick="window.location.href='16_personalities.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>BUZZFEED</h3>
        <p>A collection of engaging quizzes to uncover various personality traits and preferences.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz2.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>PSYCHCENTRAL.COM</h3>
        <p>Provides insights into your personality type and how others may perceive you.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz3.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>VERYWELL MIND</h3>
        <p>Helps identify your dominant personality traits and offers a brief analysis.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz4.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>KIDS.NATIONALGEOGRAPHIC.COM</h3>
        <p>Fun quizzes that match you with animals, explorers, or other categories based on your responses.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz5.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>PSYCHOLOGYTODAY.COM</h3>
        <p>Assesses your coping mechanisms and identifies potential areas of concern.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz6.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>HEALTHCENTRAL.COM</h3>
        <p>A variety of quizzes to explore different aspects of your personality.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz7.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>IDR Labs' Toxic Personality Test</h3>
        <p>Evaluates your level of toxic positivity and its potential impact on your well-being.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz8.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>BuzzFeed's "Which Disney Character Are You?" Quiz</h3>
        <p>Discover which Disney character aligns with your personality traits.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz9.php'">GO!</button>
    </div>
    <div class="quiz-box">
        <h3>THESUN.CO.UK</h3>
        <p>Assesses traits like narcissism, psychopathy, Machiavellianism, and sadism.</p>
        <button class="go-button" onclick="window.location.href='path/to/personality_quiz10.php'">GO!</button>
    </div>
</div>
<script>
    setTimeout(function() {
        document.getElementById("subtext").classList.add("fade-in");
    }, 4000);
</script>

</body>
</html>

