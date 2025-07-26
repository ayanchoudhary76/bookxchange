<?php
// api/get_messages.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['exchange_id'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "bookxchange";
$con = new mysqli($host, $username, $password, $database);

if ($con->connect_error) { die("Connection failed"); }

$current_user_id = $_SESSION['user_id'];
$exchange_request_id = $_GET['exchange_id'];

// Mark messages in this conversation as read
$updateStmt = $con->prepare("UPDATE messages SET is_read = 1 WHERE exchange_request_id = ? AND receiver_id = ?");
$updateStmt->bind_param("ii", $exchange_request_id, $current_user_id);
$updateStmt->execute();
$updateStmt->close();

// Fetch all messages for the conversation
$query = "SELECT
            m.id,
            m.sender_id,
            m.content,
            m.sent_at,
            u.username as sender_username
          FROM messages m
          JOIN users u ON m.sender_id = u.id
          WHERE m.exchange_request_id = ?
          ORDER BY m.sent_at ASC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $exchange_request_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($messages);

$stmt->close();
$con->close();
?>