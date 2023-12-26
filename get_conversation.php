<?php
session_start();
include("config/dbcon.php");

// Get the user ID from the AJAX request
$userId = isset($_POST['userId']) ? $_POST['userId'] : '';
$lastMessageId = isset($_POST['lastMessageId']) ? $_POST['lastMessageId'] : 0;

// Get the logged-in user's ID from session
$loggedInUserId = isset($_SESSION['auth_user']['user_id']) ? $_SESSION['auth_user']['user_id'] : '';
// Check if both user IDs are available
if ($userId && $loggedInUserId) {
    // Retrieve the conversation from the database
    $sql = "SELECT sender_id,message, timestamp FROM chat_messages 
            WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) 
            ORDER BY timestamp ASC";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("iiii", $loggedInUserId, $userId, $userId, $loggedInUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Build the conversation HTML
    $conversationHtml = '';
    while ($row = $result->fetch_assoc()) {
        $messageClass = ($row['sender_id'] == $loggedInUserId) ? 'message-sent' : 'message-received';
        $formattedTime = (new DateTime($row['timestamp']))->format('H:i'); // Adjust the format as needed
    
        $conversationHtml .= "<div class='message $messageClass'>" . htmlspecialchars($row['message']);
        $conversationHtml .= "<span class='message-time'>$formattedTime</span></div>";
    }

    // Close the statement and connection
    $stmt->close();
    $con->close();

    echo $conversationHtml;
} else {
    echo "User IDs not provided or session expired.";
}


?>

<style>
    .contact.selected {
    background-color: #ffff; /* This is actually white, if you intended a different color, replace it with the correct hex code */
    color: black;
}
.message-sent {
    color: green;
    /* Additional styling for sent messages */
}

.message-received {
    color: red;
    /* Additional styling for received messages */
}
</style>