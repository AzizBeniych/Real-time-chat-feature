<?php
// get_last_seen.php
session_start();
include("config/dbcon.php");

$userId = isset($_POST['userId']) ? $_POST['userId'] : null;

if($userId) {
    $query = "SELECT last_seen FROM users WHERE id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    $lastSeen = new DateTime($data['last_seen']);
    $formattedLastSeen = $lastSeen->format('Y-m-d H:i:s');

    echo json_encode(['last_seen' => $formattedLastSeen]);
    $stmt->close();
    $con->close();
} else {
    echo json_encode(['error' => 'User ID not provided.']);
}
?>
