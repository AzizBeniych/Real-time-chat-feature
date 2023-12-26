<?php
session_start();
include("config/dbcon.php");

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senderId = $_SESSION['auth_user']['user_id']; // The admin's session ID
    $receiverId = $_POST['userId']; // The ID of the user receiving the message
    $messageText = $_POST['message']; // The message text

    // Prepare and bind
    $stmt = $con->prepare("INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $senderId, $receiverId, $messageText);

    // Execute and check if successful
    if ($stmt->execute()) {
        // Fetch the current server time to send back to the client
        $timeQuery = "SELECT NOW() as currentTime";
        $timeResult = $con->query($timeQuery);
        $timeRow = $timeResult->fetch_assoc();
        $currentTime = $timeRow['currentTime'];
    
        // Send back a JSON response which includes both the message and the timestamp
        $response = [
            'status' => 'success',
            'message' => $messageText,
            'timestamp' => $currentTime
        ];
        echo json_encode($response);
    } else {
        // Handle errors as needed
        echo "Error: " . $con->error;
    }

    $stmt->close();
    $con->close();
} else {
    echo "Invalid request";
}
?>
