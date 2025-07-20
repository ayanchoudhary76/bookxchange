<?php
  session_start();
  if(!isset($_SESSION['user_id'])){
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

  $stmt=$con->prepare("SELECT b.title,b.author,b.isbn,g.name as genre, b.description,l.name as language, b.publication_year,b.publisher,b.page_count, ub.status,ub.added_at FROM user_books ub JOIN books b ON ub.book_id=b.id LEFT JOIN genres g ON b.genre_id=g.id LEFT JOIN languages l ON b.language_id=l.id WHERE ub.user_id=? ORDER BY ub.added_at DESC");
  $stmt->bind_param("i", $_SESSION['user_id']);
  $stmt->execute();
  $result = $stmt->get_result();
  $books = [];
  while($row = $result->fetch_assoc()) {
      $books[] = $row;
  }
  $stmt->close();
  $con->close();
  
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Books - BookXchange</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 min-h-screen">

  <!-- Navigation -->
  <nav class="bg-gray-100 shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 flex justify-between h-16 items-center">
      <h1 class="text-2xl font-bold text-indigo-600">BookXchange</h1>
      <div class="space-x-4">
        <a href="dashboard.php" class="text-gray-700 hover:text-indigo-600">Dashboard</a>
        <a href="exchange.php" class="text-gray-700 hover:text-indigo-600">Exchange</a>
        <a href="messages.php" class="text-gray-700 hover:text-indigo-600">Messages</a>
        <a href="index.php" class="text-red-600 hover:text-red-800">Logout</a>
      </div>
    </div>
  </nav>

  <!-- My Books Section -->
  <main class="max-w-7xl mx-auto py-10 px-4">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-3xl font-bold text-gray-900">My Books</h2>
      <button class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700" onclick="window.location.href='add-book.php'">Add Book</button>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php if(count($books)===0): ?>
        <div class="col-span-full text-center text-gray-500 text-lg">No books found. Start adding your books!</div>
      <?php else: ?>
        <?php foreach($books as $book): ?>
          <div class="bg-white rounded-lg shadow p-6 flex flex-col">
            <h3 class="text-xl font-bold text-indigo-700 mb-2"><?php echo htmlspecialchars($book['title']);?></h3>
            <p class="text-gray-700 mb-1"><span class="font-semibold">Author:</span><?php echo htmlspecialchars($book['author']);?></p>
            <?php if ($book['genre']): ?>
              <p class="text-gray-600 mb-1"><span class="font-semibold">Genre:</span> <?php echo htmlspecialchars($book['genre']); ?></p>
            <?php endif; ?>
            <?php if ($book['language']): ?>
              <p class="text-gray-600 mb-1"><span class="font-semibold">Language:</span> <?php echo htmlspecialchars($book['language']); ?></p>
            <?php endif; ?>
            <?php if ($book['publication_year']): ?>
              <p class="text-gray-600 mb-1"><span class="font-semibold">Year:</span> <?php echo htmlspecialchars($book['publication_year']); ?></p>
            <?php endif; ?>
            <?php if ($book['publisher']): ?>
              <p class="text-gray-600 mb-1"><span class="font-semibold">Publisher:</span> <?php echo htmlspecialchars($book['publisher']); ?></p>
            <?php endif; ?>
            <?php if ($book['page_count']): ?>
              <p class="text-gray-600 mb-1"><span class="font-semibold">Pages:</span> <?php echo htmlspecialchars($book['page_count']); ?></p>
            <?php endif; ?>
            <?php if ($book['isbn']): ?>
              <p class="text-gray-600 mb-1"><span class="font-semibold">ISBN:</span> <?php echo htmlspecialchars($book['isbn']); ?></p>
            <?php endif; ?>
            <?php if ($book['description']): ?>
              <p class="text-gray-600 mb-2"><span class="font-semibold">Description:</span> <?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
            <?php endif; ?>
            <div class="mt-auto flex justify-between items-center pt-4">
              <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold
                <?php
                  switch ($book['status']) {
                    case 'available': echo 'bg-green-100 text-green-700'; break;
                    case 'requested': echo 'bg-yellow-100 text-yellow-700'; break;
                    case 'exchanged': echo 'bg-blue-100 text-blue-700'; break;
                    case 'removed': echo 'bg-gray-200 text-gray-500'; break;
                    default: echo 'bg-gray-100 text-gray-700';
                  }
                ?>">
                <?php echo ucfirst($book['status']); ?>
              </span>
              <span class="text-xs text-gray-400"><?php echo date('M d, Y', strtotime($book['added_at'])); ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
          </div>
    </div>
  </main>
  
</body>
</html>
