<?php
    session_start(); 
    // Check if the user is logged in, using a session variable you've set at login
if (!isset($_SESSION['auth_user'])) {
    // The user is not logged in, redirect them to the login page
    header('Location: login.php');
    exit;
}
    include ("config/dbcon.php");
    //include("functions/userfunctions.php");
    include("includes/header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Chat Interface</title>
<link rel="stylesheet" href="chat.css">
<!-- Include jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<div class="chat-container">
    <div class="chat-sidebar">
        <!-- User profile -->
        <div class="profile">
            <img src="profile.jpg" alt="" class="profile-img">
            <span class="profile-name"><?php echo $_SESSION['auth_user']['name']; ?></span>
        </div>
        
        <!-- Search box -->
        <div class="search-box">
            <input type="text" placeholder="Search contacts...">
        </div>
        
        <!-- Contact list -->
        <div class="contact-list">
        <?php
// Assuming $_SESSION['auth_user']['user_id'] and $_SESSION['auth_user']['role_as'] are set
$current_user_id = $_SESSION['auth_user']['user_id'];
$current_user_role = $_SESSION['role_as'];

// Modify the SQL based on the role of the user
if ($current_user_role == 1) {
    // User is an admin, fetch all non-admin users
    $sql = "SELECT id, name, last_seen, 
            IF(last_seen > NOW() - INTERVAL 5 MINUTE, 'Online', 'Offline') AS status
            FROM users WHERE role_as = 0";
} else {
    // User is not an admin, fetch only admin users
    $sql = "SELECT id, name, last_seen, 
            IF(last_seen > NOW() - INTERVAL 5 MINUTE, 'Online', 'Offline') AS status
            FROM users WHERE role_as = 1";
}

$result = $con->query($sql);

// Check if there are any users
if ($result->num_rows > 0) {
    // Output data of each row
    while($row = $result->fetch_assoc()) {
        if ($row["id"] != $current_user_id) {
            echo "<div class='contact' data-user-id='".$row["id"]."' data-user-name='".$row["name"]."'>";
            echo "<span class='contact-name'>".$row["name"]."</span>";
            // Format the last seen time as needed
            $lastSeen = new DateTime($row['last_seen']);
            $formattedLastSeen = $lastSeen->format('Y-m-d H:i:s');
            echo "<span class='last-seen'>Last seen: " . $formattedLastSeen . "</span>";
            echo "</div>";
        }
    }
} else {
    echo "<p>No users found.</p>";
}
$con->close();

// SQL to fetch users
//$sql = "SELECT id, name, last_seen, 
//    IF(last_seen > NOW() - INTERVAL 5 MINUTE, 'Online', 'Offline') AS status
//    FROM users";
//$result = $con->query($sql);
//
//// Check if there are any users
//if ($result->num_rows > 0) {
//  // Output data of each row
//  while($row = $result->fetch_assoc()) {
//    if ($row["id"] != $_SESSION['auth_user']['user_id']) {
//    echo "<div class='contact' data-user-id='".$row["id"]."' data-user-name='".$row["name"]."'>";
//echo "<span class='contact-name'>".$row["name"]."</span>";
//// Format the last seen time as needed
//$lastSeen = new DateTime($row['last_seen']);
//$formattedLastSeen = $lastSeen->format('Y-m-d H:i:s');
//echo "<span class='last-seen'>Last seen: " . $formattedLastSeen . "</span>";
//echo "</div>";
//}
//}
//} else {
//  echo "<p>No users found.</p>";
//}
//$con->close();

?>
</div>
        
        <!-- Settings
        <div class="settings">
            <button>Add contact</button>
            <button>Settings</button>
        </div> -->
    </div>
    
    <div class="chat-main">
        <!-- Chat header -->
        <div class="chat-header">
            <img src="contact1.jpg" alt="" class="chat-header-img">
            <span class="chat-header-name">No user selected !</span>
        </div>
        
        <!-- Chat messages -->
        <div class="chat-messages">
            <!-- Messages will be dynamically loaded here -->
        </div>
        
        <!-- Message input -->
        <div class="message-input">
            <input type="text" placeholder="Write your message..." id="message-input">
            <button id="send-btn">Send</button>
        </div>
    </div>
</div>
<a href=""></a>

<script>


$(document).ready(function() {
    setInterval(function() {
            var selectedUser = $('.contact.selected'); // Assumes there's a 'selected' class on the active chat
            if(selectedUser.length) {
                selectedUser.click(); // Simulate click
            }
        }, 1000); // 1000 milliseconds = 1 second
    //connect to the WebSocket server
   // const socket = new WebSocket('ws://localhost:8080');
   //     socket.addEventListener('open', function() {
   //     socket.send('Hello, Server!');
   //     });

   //     socket.addEventListener('message', function(event) {
   //     console.log('Received: ', event.data);
   //     });

     // Event handler for sending a message
     $('#send-btn').click(function() {
    var message = $('#message-input').val();
    var userId = $('.contact.selected').data('user-id');

    if(message !== "" && userId) {
        $.ajax({
            url: 'send_message.php',
            type: 'POST',
            data: { 'userId': userId, 'message': message },
            dataType: 'json', // Expecting a JSON response
            success: function(response) {
                // Format the timestamp for display
                var timestamp = new Date(response.timestamp).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false });

                // Append both the message and the timestamp
                $('.chat-messages').append('<div class="message message-sent">' + response.message + '<div class="message-time">' + timestamp + '</div></div>');
                
                // Clear the input after sending
                $('#message-input').val(''); 
            },
            error: function(xhr, status, error) {
                console.error("An error occurred: " + status + "\nError: " + error);
            }
        });
    } else {
        alert("No user selected or message is empty");
    }
});

    // Load conversation function
    function loadConversation(userId) {
    $.ajax({
        url: 'get_conversation.php',
        type: 'POST',
        data: { 'userId': userId },
        success: function(response) {
            $('.chat-messages').html(response);
            // Scroll to the bottom of the chat messages to show the most recent
            $('.chat-messages').scrollTop($('.chat-messages')[0].scrollHeight);
        }
    });
}

    // Event handler for clicking on a contact
    $(document).on('click', '.contact', function() {


        var userId = $(this).data('user-id'); // Get the selected user ID
        var userName = $(this).data('user-name'); // Get the selected user's name
        
        $('.contact').removeClass('selected');
        $(this).addClass('selected');
        
        $('.chat-header-name').text(userName);
        loadConversation(userId);
    });


        // Function to update the last seen time
        function updateLastSeen(userId) {
        $.ajax({
            url: 'get_last_seen.php', // Path to your get_last_seen.php file
            type: 'POST',
            data: { 'userId': userId },
            success: function(response) {
                var data = JSON.parse(response);
                // Assuming you have a span with class 'last-seen-time' where you show last seen
                // This selector might need to be more specific depending on your HTML structure
                $('.last-seen-time').text('Last seen: ' + data.last_seen);
            }
        });
    }


   

});



//$(document).ready(function() {
//    // Load conversation function
//    function loadConversation(userId) {
//    $.ajax({
//        url: 'get_conversation.php',
//        type: 'POST',
//        data: { 'userId': userId },
//        success: function(response) {
//            $('.chat-messages').html(response);
//            // Scroll to the bottom of the chat messages to show the most recent
//            $('.chat-messages').scrollTop($('.chat-messages')[0].scrollHeight);
//        }
//    });
//}
//
//    // Event handler for clicking on a contact
//    $(document).on('click', '.contact', function() {
//        var userId = $(this).data('user-id'); // Get the selected user ID
//        var userName = $(this).data('user-name'); // Get the selected user's name
//        
//        $('.contact').removeClass('selected');
//        $(this).addClass('selected');
//        
//        $('.chat-header-name').text(userName);
//        loadConversation(userId);
//    });
//// Function to update the last seen time
//function updateLastSeen(userId) {
//        $.ajax({
//            url: 'get_last_seen.php', // Path to your get_last_seen.php file
//            type: 'POST',
//            data: { 'userId': userId },
//            success: function(response) {
//                var data = JSON.parse(response);
//                // Assuming you have a span with class 'last-seen-time' where you show last seen
//                // This selector might need to be more specific depending on your HTML structure
//                $('.last-seen-time').text('Last seen: ' + data.last_seen);
//            }
//        });
//    }
//    // Event handler for sending a message
//    $('#send-btn').click(function() {
//    var message = $('#message-input').val();
//    var userId = $('.contact.selected').data('user-id');
//
//    if(message !== "" && userId) {
//        $.ajax({
//            url: 'send_message.php',
//            type: 'POST',
//            data: { 'userId': userId, 'message': message },
//            dataType: 'json', // Expecting a JSON response
//            success: function(response) {
//                // Format the timestamp for display
//                var timestamp = new Date(response.timestamp).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', hour12: false });
//
//                // Append both the message and the timestamp
//                $('.chat-messages').append('<div class="message message-sent">' + response.message + '<div class="message-time">' + timestamp + '</div></div>');
//                
//                // Clear the input after sending
//                $('#message-input').val(''); 
//            },
//            error: function(xhr, status, error) {
//                console.error("An error occurred: " + status + "\nError: " + error);
//            }
//        });
//    } else {
//        alert("No user selected or message is empty");
//    }
//});
//
//});

</script>
</script>
<?php include("includes/newfooter.php") ?>
</body>
</html>
<style>
    .contact.selected {
    background-color: #ffff; /* This is actually white, if you intended a different color, replace it with the correct hex code */
    color: black;
    cursor: pointer;
}
.contact {
    cursor: pointer;
}
.contact:hover {
    color:black;
}
body {
    display: block;
}
.chat-container {
    margin: 44px;
    margin-left: 12%;
}
.contact {
    /* Existing styles */
    padding: 10px;
    border-bottom: 1px solid #eee; /* Optional: adds a line between contacts */
    display: flex;
    flex-direction: column; /* Stack children elements vertically */
    align-items: flex-start; /* Align children to the start of the cross axis */
}

.contact-name {
    /* Existing styles */
    font-weight: bold;
    margin-bottom: 5px; /* Add some space between the name and last seen text */
}

.last-seen {
    font-size: 0.7em;
    color: #888;
    margin-top: 2px;
    align-self: flex-start; /* Align to the start on the cross axis, ensuring it's below the name */
}

.contact-list {
    max-height: 400px; /* Adjust as needed for your layout */
    overflow-y: auto; /* Enables vertical scrolling */
    overflow-x: hidden; /* Hides horizontal scrollbar */
}
/* Chat message bubbles styling */
.message {
    max-width: 60%; /* Maximum width of message bubble */
    margin-bottom: 10px;
    padding: 10px;
    border-radius: 20px; /* Rounded corners for bubble effect */
    position: relative; /* For positioning the timestamp */
    color: white; /* Default text color */
    padding-bottom: 30px; /* Increased padding to make space for timestamp */
}

/* Sent message styling */
.message-received{
    background-color: #0084FF; /* Facebook Messenger-like blue */
    margin-left: auto; /* Aligns the message to the right */
    text-align: left; /* Aligns text inside the bubble to the right */
}

/* Received message styling */
.message-sent  {
    background-color: #E5E5EA; /* Light grey background */
    text-align: left; /* Aligns text inside the bubble to the left */
    color: black; /* Text color for received messages */
}

/* Timestamp styling */
.message-time {
    font-size: 0.75rem; /* Smaller font size for timestamp */
    position: absolute; /* Positioning relative to the message bubble */
    bottom: 5px; /* Position from the bottom of the message bubble */
    display: block; /* Ensures it takes up its own line */
}

 .message-received .message-time{
    /* right: 10px; */
    text-align: left;
}

.message-sent .message-time{
    left: 10px; /* Align timestamp to the left for received messages */
}


</style>


