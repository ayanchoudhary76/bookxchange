<?php
session_start();

// ====== 1. Clear the Persistent "Remember Me" Cookie ======
// This part is crucial for the bug you found. It deletes the token 
// from the database and unsets the cookie.

if (isset($_COOKIE['session_token'])) {
    // Database connection details
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "bookxchange";
    $con = new mysqli($host, $username, $password, $database);

    if ($con->connect_error) {
        // Even if DB connection fails, proceed to destroy the session
    } else {
        // Prepare a statement to delete the specific token from the database
        $stmt = $con->prepare("DELETE FROM user_sessions WHERE session_token = ?");
        $stmt->bind_param("s", $_COOKIE['session_token']);
        $stmt->execute();
        $stmt->close();
        $con->close();
    }
    
    // Unset the cookie by setting its expiration to the past
    unset($_COOKIE['session_token']);
    setcookie('session_token', '', time() - 3600, '/'); // The '/' path ensures it's cleared for the whole domain
}


// ====== 2. Unset all of the session variables ======
$_SESSION = array();


// ====== 3. Destroy the session ======
session_destroy();


// ====== 4. Redirect to the login page ======
header("location: index.php");
exit();
?>