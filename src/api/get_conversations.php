<?php
// api/get_conversations.php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit();
}

$host = "localhost";
$username = "root";
$password = "";
$database = "bookxchange";
$con = new mysqli($host, $username, $password, $database);

if ($con->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$current_user_id = $_SESSION['user_id'];

// This query gets all unique exchange requests the user is part of,
// along with the other user's name and the title of the requested book.
$query = "SELECT DISTINCT
            er.id as exchange_request_id,
            b.title as book_title,
            CASE
                WHEN er.requester_id = ? THEN u_owner.username
                ELSE u_requester.username
            END as other_user,
            CASE
                WHEN er.requester_id = ? THEN er.owner_id
                ELSE er.requester_id
            END as other_user_id,
            (SELECT content FROM messages m WHERE m.exchange_request_id = er.id ORDER BY m.sent_at DESC LIMIT 1) as last_message,
            (SELECT COUNT(*) FROM messages m WHERE m.exchange_request_id = er.id AND m.receiver_id = ? AND m.is_read = 0) as unread_count
          FROM exchange_requests er
          JOIN user_books ub ON er.user_book_id = ub.id
          JOIN books b ON ub.book_id = b.id
          JOIN users u_requester ON er.requester_id = u_requester.id
          JOIN users u_owner ON er.owner_id = u_owner.id
          WHERE er.requester_id = ? OR er.owner_id = ?
          ORDER BY (SELECT sent_at FROM messages m WHERE m.exchange_request_id = er.id ORDER BY m.sent_at DESC LIMIT 1) DESC";


$stmt = $con->prepare($query);
$stmt->bind_param("iiiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$conversations = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($conversations);

$stmt->close();
$con->close();
?>