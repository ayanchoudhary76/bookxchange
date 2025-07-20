<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}

// Check if the book ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Error: Invalid request.";
    header("location: my-books.php");
    exit();
}

$user_book_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$host = "localhost";
$username = "root";
$password = "";
$database = "bookxchange";

// Create a new database connection
$con = new mysqli($host, $username, $password, $database);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Prepare and execute the DELETE statement
// This ensures that users can only delete their own books
$stmt = $con->prepare("DELETE FROM user_books WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $user_book_id, $user_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $_SESSION['message'] = "Successfully removed the book.";
    } else {
        $_SESSION['message'] = "Error: Book not found or you do not have permission to remove it.";
    }
} else {
    $_SESSION['message'] = "Error: Could not remove the book.";
}

$stmt->close();
$con->close();

// Redirect back to the my-books page
header("location: my-books.php");
exit();
?>