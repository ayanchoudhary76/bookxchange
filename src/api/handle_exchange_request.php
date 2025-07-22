<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Database connection details
$host = "localhost";
$username = "root";
$password = "";
$database = "bookxchange";
$con = new mysqli($host, $username, $password, $database);

if ($con->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$current_user_id = $_SESSION['user_id'];
$con->begin_transaction();

try {
    if ($action === 'create') {
        $user_book_id = $data['user_book_id'];
        $owner_id = $data['owner_id'];
        $offered_book_id = $data['offered_book_id'];
        $message = $data['message'];

        if (empty($offered_book_id)) {
            throw new Exception('You must offer a book to make an exchange request.');
        }

        $stmt = $con->prepare("INSERT INTO exchange_requests (requester_id, owner_id, user_book_id, offered_book_id, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiis", $current_user_id, $owner_id, $user_book_id, $offered_book_id, $message);
        $stmt->execute();

        $updateStmt = $con->prepare("UPDATE user_books SET status = 'requested' WHERE id = ? OR id = ?");
        $updateStmt->bind_param("ii", $user_book_id, $offered_book_id);
        $updateStmt->execute();

        $con->commit();
        echo json_encode(['success' => true, 'message' => 'Exchange request sent successfully!']);

    } elseif ($action === 'accept') {
        
        $request_id = $data['request_id'];

        // 1. Get all details of the accepted request
        $reqStmt = $con->prepare("SELECT requester_id, owner_id, user_book_id, offered_book_id FROM exchange_requests WHERE id = ? AND owner_id = ? AND status = 'pending'");
        $reqStmt->bind_param("ii", $request_id, $current_user_id);
        $reqStmt->execute();
        $request = $reqStmt->get_result()->fetch_assoc();
        $reqStmt->close();

        if (!$request) {
            throw new Exception('Request not found, is already handled, or you are not the owner.');
        }

        $requester_id = $request['requester_id'];
        $owner_id = $request['owner_id'];
        $requested_book_id = $request['user_book_id'];
        $offered_book_id = $request['offered_book_id'];

        // 2. SWAP OWNERSHIP & SET STATUS TO 'EXCHANGED'
        $swap1 = $con->prepare("UPDATE user_books SET user_id = ?, status = 'exchanged' WHERE id = ?");
        $swap1->bind_param("ii", $requester_id, $requested_book_id);
        $swap1->execute();
        $swap1->close();

        $swap2 = $con->prepare("UPDATE user_books SET user_id = ?, status = 'exchanged' WHERE id = ?");
        $swap2->bind_param("ii", $owner_id, $offered_book_id);
        $swap2->execute();
        $swap2->close();

        // 3. Mark the accepted request as 'completed'
        $updateReq = $con->prepare("UPDATE exchange_requests SET status = 'completed' WHERE id = ?");
        $updateReq->bind_param("i", $request_id);
        $updateReq->execute();
        $updateReq->close();

        // 4. --- CRITICAL FIX: CLEAN UP OTHER PENDING REQUESTS ---
        // First, find all other books that are now locked by pending requests involving the two swapped books.
        $findLockedBooksStmt = $con->prepare(
            "SELECT DISTINCT ub.id FROM user_books ub
             JOIN exchange_requests er ON (ub.id = er.user_book_id OR ub.id = er.offered_book_id)
             WHERE (er.user_book_id = ? OR er.offered_book_id = ? OR er.user_book_id = ? OR er.offered_book_id = ?) AND er.status = 'pending'"
        );
        $findLockedBooksStmt->bind_param("iiii", $requested_book_id, $requested_book_id, $offered_book_id, $offered_book_id);
        $findLockedBooksStmt->execute();
        $locked_books_result = $findLockedBooksStmt->get_result();
        $locked_book_ids = [];
        while($row = $locked_books_result->fetch_assoc()) {
            $locked_book_ids[] = $row['id'];
        }
        $findLockedBooksStmt->close();

        // Now, reject all other pending requests for the two swapped books.
        $rejectStmt = $con->prepare("UPDATE exchange_requests SET status = 'rejected' WHERE (user_book_id = ? OR offered_book_id = ? OR user_book_id = ? OR offered_book_id = ?) AND status = 'pending'");
        $rejectStmt->bind_param("iiii", $requested_book_id, $requested_book_id, $offered_book_id, $offered_book_id);
        $rejectStmt->execute();
        $rejectStmt->close();

        // Finally, if any books were locked, set their status back to 'available'.
        if (!empty($locked_book_ids)) {
            $placeholders = implode(',', array_fill(0, count($locked_book_ids), '?'));
            $types = str_repeat('i', count($locked_book_ids));
            $resetStatusStmt = $con->prepare("UPDATE user_books SET status = 'available' WHERE id IN ($placeholders)");
            $resetStatusStmt->bind_param($types, ...$locked_book_ids);
            $resetStatusStmt->execute();
            $resetStatusStmt->close();
        }

        $con->commit();
        echo json_encode(['success' => true, 'message' => 'Exchange successful! The books have been swapped.']);

    } elseif ($action === 'reject' || $action === 'cancel') {
        // This part was already working correctly but is included for completeness.
        $request_id = $data['request_id'];
        
        $user_check_field = ($action === 'reject') ? 'owner_id' : 'requester_id';
        $sql = "SELECT user_book_id, offered_book_id FROM exchange_requests WHERE id = ? AND $user_check_field = ?";
        
        $bookIdStmt = $con->prepare($sql);
        $bookIdStmt->bind_param("ii", $request_id, $current_user_id);
        $bookIdStmt->execute();
        $result = $bookIdStmt->get_result();
        if($result->num_rows === 0) {
            throw new Exception('Request not found or you do not have permission.');
        }
        $books = $result->fetch_assoc();
        $user_book_id = $books['user_book_id'];
        $offered_book_id = $books['offered_book_id'];

        if ($action === 'cancel') {
            $updateReqStmt = $con->prepare("DELETE FROM exchange_requests WHERE id = ?");
        } else {
            $updateReqStmt = $con->prepare("UPDATE exchange_requests SET status = 'rejected' WHERE id = ?");
        }
        $updateReqStmt->bind_param("i", $request_id);
        $updateReqStmt->execute();
        
        $updateBookStmt = $con->prepare("UPDATE user_books SET status = 'available' WHERE id = ? OR id = ?");
        $updateBookStmt->bind_param("ii", $user_book_id, $offered_book_id);
        $updateBookStmt->execute();
        
        $con->commit();
        $message = ($action === 'cancel') ? 'Request successfully cancelled.' : 'Request rejected.';
        echo json_encode(['success' => true, 'message' => $message]);

    } else {
        throw new Exception('Invalid action.');
    }

} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
}

$con->close();

?>