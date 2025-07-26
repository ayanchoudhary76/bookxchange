<?php
// api/send_message.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "bookxchange";
$con = new mysqli($host, $username, $password, $database);

if ($con->connect_error) { die("Connection failed"); }

$data = json_decode(file_get_contents('php://input'), true);

$sender_id = $_SESSION['user_id'];
$receiver_id = $data['receiver_id'] ?? 0;
$exchange_request_id = $data['exchange_request_id'] ?? 0;
$content = trim($data['content'] ?? '');

if (empty($receiver_id) || empty($exchange_request_id) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
    exit();
}

// Security Check: Make sure the sender is part of the exchange
$securityCheck = $con->prepare("SELECT id FROM exchange_requests WHERE id = ? AND (requester_id = ? OR owner_id = ?)");
$securityCheck->bind_param("iii", $exchange_request_id, $sender_id, $sender_id);
$securityCheck->execute();
if ($securityCheck->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to post in this conversation.']);
    exit();
}
$securityCheck->close();


$stmt = $con->prepare("INSERT INTO messages (sender_id, receiver_id, exchange_request_id, content) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iiis", $sender_id, $receiver_id, $exchange_request_id, $content);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send message.']);
}

$stmt->close();
$con->close();
?>