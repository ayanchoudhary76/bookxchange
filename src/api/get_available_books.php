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

$current_user_id = $_SESSION['user_id'];

$query = "SELECT 
            ub.id as user_book_id,
            b.title,
            b.author,
            b.description,
            b.isbn,
            u.username as owner,
            ub.user_id as owner_id
          FROM user_books ub
          JOIN books b ON ub.book_id = b.id
          JOIN users u ON ub.user_id = u.id
          WHERE ub.status = 'available' AND ub.user_id != ?";

$stmt = $con->prepare($query);
$stmt->bind_param("i", $current_user_id);
$stmt->execute();
$result = $stmt->get_result();
$books = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($books);

$stmt->close();
$con->close();
?>