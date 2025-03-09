<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User is not logged in']);
    exit();
}

global $conn;
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'db'; // Your database name
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data from AJAX request
$user_id = $_POST['user_id'];
$quiz_id = $_POST['quiz_id'];
$result = $_POST['result'];
$timestamp = $_POST['timestamp']; // Use timestamp sent from frontend or let DB handle it

// Prepare and execute the insert query
$sql = "INSERT INTO user_quizzes (user_id, quiz_id, result, result_date) 
        VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiss", $user_id, $quiz_id, $result, $timestamp);

// Execute the query and check for success
if ($stmt->execute()) {
    // Respond back to the client with a success message
    echo json_encode(['status' => 'success', 'message' => 'Quiz result saved successfully']);
} else {
    // Respond with an error message if the insert failed
    echo json_encode(['status' => 'error', 'message' => 'Failed to save result']);
}

$stmt->close();
$conn->close();
?>
