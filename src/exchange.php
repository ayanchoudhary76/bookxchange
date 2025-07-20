<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Exchange - BookXchange</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 min-h-screen">

  <!-- Navigation -->
  <nav class="bg-gray-100 shadow-sm border-b">
    <div class="max-w-7xl mx-auto px-4 flex justify-between h-16 items-center">
      <h1 class="text-2xl font-bold text-indigo-600">BookXchange</h1>
      <div class="space-x-4">
        <a href="dashboard.php" class="text-gray-700 hover:text-indigo-600">Dashboard</a>
        <a href="my-books.php" class="text-gray-700 hover:text-indigo-600">My Books</a>
        <a href="messages.php" class="text-gray-700 hover:text-indigo-600">Messages</a>
        <a href="index.php" class="text-red-600 hover:text-red-800">Logout</a>
      </div>
    </div>
  </nav>

  <!-- Exchange Section -->
  <main class="max-w-7xl mx-auto py-10 px-4">
    <h2 class="text-3xl font-bold text-gray-900 mb-6">Book Exchange</h2>

    <!-- Tabs -->
    <div class="flex space-x-6 mb-6" id="exchange-tabs">
      <button class="tab-btn text-indigo-600 border-b-2 border-indigo-600 pb-1" data-tab="available">Available Books</button>
      <button class="tab-btn text-gray-600 hover:text-indigo-600 hover:border-b-2" data-tab="my-requests">My Requests</button>
      <button class="tab-btn text-gray-600 hover:text-indigo-600 hover:border-b-2" data-tab="incoming">Incoming Requests</button>
    </div>



    <div id="tab-available" class="tab-content grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    </div>
    <div id="tab-my-requests" class="tab-content hidden">

    </div>
    <div id="tab-incoming" class="tab-content hidden">

    </div>
    <!-- Book Grid Placeholder -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Exchange book cards go here -->
    </div>
  </main>
  <script>
    const tabBtns =document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn=>{
      btn.addEventListener('click',()=>{
        tabBtns.forEach(b=>{
          b.classList.remove('text-indigo-600', 'border-b-2', 'border-indigo-600');
        });
        tabBtns.forEach(b=>{
          b.classList.add('text-gray-600', 'hover:text-indigo-600', 'hover:border-b-2');
        });
        btn.classList.remove('text-gray-600', 'hover:text-indigo-600', 'hover:border-b-2');
        btn.classList.add('text-indigo-600', 'border-b-2', 'border-indigo-600');
        tabContents.forEach(tc=>tc.classList.add('hidden'));
        document.getElementById('tab-'+btn.dataset.tab).classList.remove('hidden');
      });
    });
  </script>
</body>
</html>
