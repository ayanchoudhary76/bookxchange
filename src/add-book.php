<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "bookxchange";
$con = new mysqli($host, $username, $password, $database);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $language = trim($_POST['language'] ?? '');
    $publication_year = trim($_POST['publication_year'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $page_count = trim($_POST['page_count'] ?? '');

    // Basic validation
    if ($title === '' || $author === '') {
        echo "<script>alert('Title and Author are required fields.');</script>";
    } else {
        // Handle genre
        $genre_id = null;
        if ($genre !== '') {
            $stmt = $con->prepare("SELECT id FROM genres WHERE name = ?");
            $stmt->bind_param("s", $genre);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row) {
                $genre_id = $row['id'];
            } else {
                $stmt = $con->prepare("INSERT INTO genres (name) VALUES (?)");
                $stmt->bind_param("s", $genre);
                $stmt->execute();
                $genre_id = $con->insert_id;
            }
        }

        // Handle language
        $language_id = null;
        if ($language !== '') {
            $stmt = $con->prepare("SELECT id FROM languages WHERE name = ?");
            $stmt->bind_param("s", $language);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row) {
                $language_id = $row['id'];
            } else {
                $stmt = $con->prepare("INSERT INTO languages (name) VALUES (?)");
                $stmt->bind_param("s", $language);
                $stmt->execute();
                $language_id = $con->insert_id;
            }
        }

        // Handle null values safely
        $isbn_or_null = $isbn !== '' ? $isbn : null;
        $description_or_null = $description !== '' ? $description : null;
        $publication_year_or_null = $publication_year !== '' ? $publication_year : null;
        $publisher_or_null = $publisher !== '' ? $publisher : null;
        $page_count_or_null = $page_count !== '' ? $page_count : null;

        // Insert book
        $stmt = $con->prepare("INSERT INTO books (title, author, isbn, genre_id, description, language_id, publication_year, publisher, page_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssisisii",
            $title,
            $author,
            $isbn_or_null,
            $genre_id,
            $description_or_null,
            $language_id,
            $publication_year_or_null,
            $publisher_or_null,
            $page_count_or_null
        );
        $stmt->execute();

        // Insert into user_books
        $book_id = $con->insert_id;
        $stmt = $con->prepare("INSERT INTO user_books (user_id, book_id, status) VALUES (?, ?, 'available')");
        $stmt->bind_param("ii", $_SESSION['user_id'], $book_id);
        $stmt->execute();

        echo "<script>alert('Book added successfully!'); window.location.href='my-books.php';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Book - BookXchange</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<script>
  const form = document.querySelector('form');
  const submitBtn = form.querySelector('button[type="submit"]');

  form.addEventListener('submit', function(e) {
    // Prevent multiple submissions
    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';

    // validation for publication year
    const year = form.publication_year.value;
    if (year && (year < 1000 || year > 9999)) {
      alert('Please enter a valid publication year (1000-9999).');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Add Book';
      e.preventDefault();
      return;
    }

    // validation for page count
    const pageCount = form.page_count.value;
    if (pageCount && pageCount < 1) {
      alert('Page count must be at least 1.');
      submitBtn.disabled = false;
      submitBtn.textContent = 'Add Book';
      e.preventDefault();
      return;
    }
  });
</script>
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

  <!-- Add Book Form -->
  <main class="max-w-2xl mx-auto py-10 px-4">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Add a New Book</h2>
    <form action="add-book.php" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
      <div>
        <label for="title" class="block text-gray-700 mb-2">Title <span class="text-red-500">*</span></label>
        <input type="text" id="title" name="title" required class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label for="author" class="block text-gray-700 mb-2">Author <span class="text-red-500">*</span></label>
        <input type="text" id="author" name="author" required class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label for="isbn" class="block text-gray-700 mb-2">ISBN</label>
        <input type="text" id="isbn" name="isbn" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label for="genre" class="block text-gray-700 mb-2">Genre</label>
        <input type="text" id="genre" name="genre" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label for="description" class="block text-gray-700 mb-2">Description</label>
        <textarea id="description" name="description" rows="4" class="w-full border rounded px-3 py-2"></textarea>
      </div>
      <div>
        <label for="language" class="block text-gray-700 mb-2">Language</label>
        <input type="text" id="language" name="language" value="English" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label for="publication_year" class="block text-gray-700 mb-2">Publication Year</label>
        <input type="number" id="publication_year" name="publication_year" min="1000" max="9999" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label for="publisher" class="block text-gray-700 mb-2">Publisher</label>
        <input type="text" id="publisher" name="publisher" class="w-full border rounded px-3 py-2">
      </div>
      <div>
        <label for="page_count" class="block text-gray-700 mb-2">Page Count</label>
        <input type="number" id="page_count" name="page_count" min="1" class="w-full border rounded px-3 py-2">
      </div>
      <div class="flex justify-end space-x-2">
        <a href="my-books.php" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">Cancel</a>
        <button type="submit" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700">Add Book</button>
      </div>
    </form>
    </main>
    </body>
</html>