<?php
  session_start();
  if(!isset($_SESSION['user_id'])) {
      header("location: index.php");
      exit();
  }
  $host="localhost";
  $username="root";
  $password="";
  $database="bookxchange";
  $con = new mysqli($host, $username, $password, $database);
  if($con->connect_error) {
      die("Connection failed: " . $con->connect_error);
  }
  // My Books count
$stmt = $con->prepare("SELECT COUNT(*) FROM user_books WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$book_count = $result->fetch_row()[0];
$stmt->close();

// Active Exchanges count
$stmt = $con->prepare("SELECT COUNT(*) FROM exchange_requests WHERE owner_id = ? AND status = 'active'");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$active_exchanges = $result->fetch_row()[0];
$stmt->close();

// Messages count
$stmt = $con->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$msg_count = $result->fetch_row()[0];
$stmt->close();

$con->close();

echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('my-books').textContent = '$book_count';
    document.getElementById('active-exchanges').textContent = '$active_exchanges';
    document.getElementById('msg').textContent = '$msg_count';
});
</script>";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - BookXchange</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 min-h-screen">

  <!-- Navigation -->
  <nav class="bg-gray-100 shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 flex justify-between h-16 items-center">
      <h1 class="text-2xl font-bold text-indigo-600">BookXchange</h1>
      <div class="space-x-4">
        <a href="my-books.php" class="text-gray-700 hover:text-indigo-600">My Books</a>
        <a href="exchange.php" class="text-gray-700 hover:text-indigo-600">Exchange</a>
        <a href="messages.php" class="text-gray-700 hover:text-indigo-600">Messages</a>
        <a href="index.php" class="text-red-600 hover:text-red-800">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Dashboard Content -->
  <main class="max-w-7xl mx-auto py-10 px-4">
    <h2 class="text-3xl font-bold text-gray-900 mb-4">Welcome back, User!</h2>
    <p class="text-gray-600 mb-8">Manage your books and exchanges</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-900">My Books</h3>
        <p class="text-3xl font-bold text-indigo-600" id="my-books">0</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-900">Active Exchanges</h3>
        <p class="text-3xl font-bold text-green-600" id="active-exchanges">0</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold text-gray-900">Messages</h3>
        <p class="text-3xl font-bold text-blue-600" id="msg">0</p>
      </div>
    </div>
  </main>
</body>
</html>
