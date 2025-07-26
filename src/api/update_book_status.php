<?php
/**
 * api/update_book_status.php
 * Updates a book's status from 'exchanged' to 'available'.
 */

// FIX: Safely start the session to get the logged-in user's ID.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set the content type to JSON for the response.
header('Content-Type: application/json');

// --- Validation and Security ---
if (!isset($_SESSION['user_id'])) {
    // Immediately exit if the user is not logged in.
    echo json_encode(['success' => false, 'message' => 'Error: You must be logged in to perform this action.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_book_id = $data['user_book_id'] ?? 0;
$current_user_id = $_SESSION['user_id'];

if (empty($user_book_id)) {
    // Exit if the book ID was not sent from the JavaScript.
    echo json_encode(['success' => false, 'message' => 'Error: Invalid request, book ID is missing.']);
    exit();
}

// --- Database Connection ---
$host = "localhost";
$username = "root";
$password = "";
$database = "bookxchange";
$con = new mysqli($host, $username, $password, $database);

if ($con->connect_error) {
    // Exit if the database connection fails.
    echo json_encode(['success' => false, 'message' => 'Error: Database connection failed.']);
    exit();
}

// --- Main Logic ---
try {
    // Prepare an update statement that also checks for ownership (user_id).
    // This is a critical security measure.
    $stmt = $con->prepare("UPDATE user_books SET status = 'available' WHERE id = ? AND user_id = ? AND status = 'exchanged'");
    $stmt->bind_param("ii", $user_book_id, $current_user_id);
    
    $stmt->execute();

    // Check if any row was actually updated.
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Success! The book is now available for exchange.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Could not update the book. It might already be available or you do not have permission.']);
    }
    
    $stmt->close();

} catch (Exception $e) {
    // Catch any unexpected database errors.
    echo json_encode(['success' => false, 'message' => 'A server error occurred.']);
}

$con->close();

?>