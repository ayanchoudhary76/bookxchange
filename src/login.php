<?php
  session_start();
  if(isset($_SESSION['user_id'])) {
      header("location: dashboard.php");
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
  if($_SERVER['REQUEST_METHOD'] == 'POST') {
      $email = trim($_POST['email'] ?? '');
      $password = trim($_POST['password'] ?? '');

      if($email === '' || $password === '') {
          echo "<script>alert('Email and Password are required fields.');</script>";
      } else {
          $stmt = $con->prepare("SELECT id, password_hash FROM users WHERE email = ?");
          $stmt->bind_param("s", $email);
          $stmt->execute();
          $result = $stmt->get_result();

          if($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              if(password_verify($password, $row['password_hash'])) {
                  $_SESSION['user_id'] = $row['id'];
                  header("location: dashboard.php");
                  exit();
              } else {
                  echo "<script>alert('Invalid email or password.');</script>";
              }
          } else {
              echo "<script>alert('No user found with this email.');</script>";
          }
          $stmt->close();
      }
  }
  $con->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - BookXchange</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 min-h-screen">

  <!-- Navigation -->
  <nav class="bg-gray-100 shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 flex justify-between h-16 items-center">
      <h1 class="text-2xl font-bold text-indigo-600">BookXchange</h1>
      <div class="space-x-4">
        <a href="index.php" class="text-gray-700 hover:text-indigo-600">Home</a>
        <a href="register.php" class="text-indigo-600 hover:text-indigo-800">Register</a>
      </div>
    </div>
  </nav>

  <!-- Login Form -->
  <main class="max-w-md mx-auto py-12">
    <div class="bg-white shadow-lg rounded-lg p-8">
      <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
      <form action="" method="POST">
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
          <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
          <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">Login</button>
      </form>
      <p class="text-center mt-4">
        Don’t have an account?
        <a href="register.php" class="text-indigo-600 hover:text-indigo-800">Register here</a>
      </p>
    </div>
  </main>

</body>
</html>
