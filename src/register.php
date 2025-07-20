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
      $username = trim($_POST['username'] ?? '');
      $email = trim($_POST['email'] ?? '');
      $password = trim($_POST['password'] ?? '');
      $confirm_password = trim($_POST['confirm_password'] ?? '');

      if($password !== $confirm_password) {
          echo "<script>alert('Passwords do not match.');</script>";
      } else {
          $stmt = $con->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
          $hashed_password = password_hash($password, PASSWORD_DEFAULT);
          $stmt->bind_param("sss", $username, $email, $hashed_password);
          if($stmt->execute()) {
              echo "<script>alert('Registration successful! You can now log in.');</script>";
              header("location: login.php");
              exit();
          } else {
              echo "<script>alert('Registration failed. Please try again.');</script>";
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
  <title>Register - BookXchange</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 min-h-screen">

  <!-- Navigation -->
  <nav class="bg-gray-100 shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 flex justify-between h-16 items-center">
      <h1 class="text-2xl font-bold text-indigo-600">BookXchange</h1>
      <div class="space-x-4">
        <a href="index.php" class="text-gray-700 hover:text-indigo-600">Home</a>
        <a href="login.php" class="text-indigo-600 hover:text-indigo-800">Login</a>
      </div>
    </div>
  </nav>

  <!-- Register Form -->
  <main class="max-w-md mx-auto py-12">
    <div class="bg-white shadow-lg rounded-lg p-8">
      <h2 class="text-2xl font-bold text-center mb-6">Register</h2>
      <form action="" method="POST">
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Username</label>
          <input type="text" name="username" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
          <input type="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="mb-4">
          <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
          <input type="password" name="password" required minlength="8" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Confirm Password</label>
          <input type="password" name="confirm_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">Register</button>
      </form>
      <p class="text-center mt-4">
        Already have an account?
        <a href="login.php" class="text-indigo-600 hover:text-indigo-800">Login here</a>
      </p>
    </div>
  </main>
  <script>
  // Prevent multiple submissions and check password match
  const form = document.querySelector('form');
  const submitBtn = form.querySelector('button[type="submit"]');
  form.addEventListener('submit', function(e) {
    const password = form.password.value;
    const confirm = form.confirm_password.value;
    if (password !== confirm) {
      alert('Passwords do not match.');
      e.preventDefault();
      return;
    }
    submitBtn.disabled = true;
    submitBtn.textContent = 'Registering...';
  });
</script>
</body>
</html>
