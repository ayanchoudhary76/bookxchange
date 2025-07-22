<?php
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

$owner_id = $_SESSION['user_id'];

$query = "SELECT
            er.id as request_id,
            er.status,
            b.title as requested_book_title,
            u_requester.username as requester_username,
            er.message,
            er.requested_at
          FROM exchange_requests er
          JOIN user_books ub ON er.user_book_id = ub.id
          JOIN books b ON ub.book_id = b.id
          JOIN users u_requester ON er.requester_id = u_requester.id
          WHERE er.owner_id = ?
          ORDER BY er.requested_at DESC";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();
$requests = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($requests);

$stmt->close();
$con->close();
?>