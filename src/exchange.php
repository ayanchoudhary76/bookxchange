<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Exchange - BookXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-200 min-h-screen">

    <nav class="bg-gray-100 shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 flex justify-between h-16 items-center">
            <h1 class="text-2xl font-bold text-indigo-600">BookXchange</h1>
            <div class="space-x-4">
                <a href="dashboard.php" class="text-gray-700 hover:text-indigo-600">Dashboard</a>
                <a href="my-books.php" class="text-gray-700 hover:text-indigo-600">My Books</a>
                <a href="messages.php" class="text-gray-700 hover:text-indigo-600">Messages</a>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto py-10 px-4">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Book Exchange</h2>
        <div class="flex space-x-6 mb-6 border-b" id="exchange-tabs">
            <button class="tab-btn text-indigo-600 border-b-2 border-indigo-600 pb-2 font-semibold" data-tab="available">Available Books</button>
            <button class="tab-btn text-gray-500 hover:text-indigo-600 pb-2 font-semibold" data-tab="my-requests">My Requests</button>
            <button class="tab-btn text-gray-500 hover:text-indigo-600 pb-2 font-semibold" data-tab="incoming">Incoming Requests</button>
        </div>
        <div id="tab-content-area"></div>
    </main>

    <div id="exchangeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Send Exchange Request</h3>
                <div class="mt-2 px-7 py-3 text-left">
                    <p class="text-sm text-gray-600 mb-4" id="modal-book-title"></p>
                    
                    <label for="offerBookSelect" class="block text-sm font-medium text-gray-700">Select a book to offer:</label>
                    <select id="offerBookSelect" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                        </select>
                    
                    <textarea id="exchangeMessage" class="mt-4 w-full px-3 py-2 text-gray-700 border rounded-lg focus:outline-none" rows="3" placeholder="Optional: Add a message..."></textarea>
                </div>
                <div class="items-center px-4 py-3">
                    <input type="hidden" id="modal_user_book_id">
                    <input type="hidden" id="modal_owner_id">
                    <button id="sendRequestBtn" class="px-4 py-2 bg-indigo-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-300">
                        Send Request
                    </button>
                    <button id="closeModalBtn" class="mt-3 px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300 focus:outline-none">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContentArea = document.getElementById('tab-content-area');
        const modal = document.getElementById('exchangeModal');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const sendRequestBtn = document.getElementById('sendRequestBtn');
        const offerBookSelect = document.getElementById('offerBookSelect');
        let currentTab = 'available';

        // --- THIS IS THE CORE TAB NAVIGATION LOGIC ---
        // It attaches a click event to each tab button.
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // 1. Remove active styles from all tabs
                tabBtns.forEach(b => {
                    b.classList.remove('text-indigo-600', 'border-indigo-600', 'border-b-2');
                    b.classList.add('text-gray-500');
                });

                // 2. Add active styles to the clicked tab
                btn.classList.add('text-indigo-600', 'border-indigo-600', 'border-b-2');
                btn.classList.remove('text-gray-500');

                // 3. Load the content for the clicked tab's dataset
                loadTabContent(btn.dataset.tab);
            });
        });

        async function loadTabContent(tabName) {
            currentTab = tabName;
            tabContentArea.innerHTML = `<div class="text-center p-10">Loading...</div>`;
            let apiUrl;
            if (tabName === 'available') apiUrl = 'api/get_available_books.php';
            else if (tabName === 'my-requests') apiUrl = 'api/get_my_requests.php';
            else if (tabName === 'incoming') apiUrl = 'api/get_incoming_requests.php';
            else {
                tabContentArea.innerHTML = `<div class="text-center text-red-500 p-10">Invalid tab.</div>`;
                return;
            }
            try {
                const response = await fetch(apiUrl);
                const data = await response.json();
                if (data.error) {
                    tabContentArea.innerHTML = `<div class="text-center text-red-500 p-10">${data.error}</div>`;
                    return;
                }
                renderContent(tabName, data);
            } catch (error) {
                tabContentArea.innerHTML = `<div class="text-center text-red-500 p-10">Failed to load content. Please try again.</div>`;
                console.error('Error fetching tab content:', error);
            }
        }

        function renderContent(tabName, data) {
            let html = '<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">';
                if (data.length === 0) {
                    html = '<div class="col-span-full text-center text-gray-500 text-lg mt-8">No items to display.</div>';
                } else {
                    if (tabName === 'available') {
                        data.forEach(book => {
                            html += `
                                <div class="bg-white rounded-lg shadow p-6 flex flex-col">
                                    <h3 class="text-xl font-bold text-indigo-700 mb-2">${book.title}</h3>
                                    <p class="text-gray-700 mb-1"><span class="font-semibold">Author:</span> ${book.author}</p>
                                    <p class="text-gray-600 mb-1"><span class="font-semibold">Owner:</span> ${book.owner}</p>
                                    <p class="text-gray-600 mt-2 text-sm">${book.description ? book.description.substring(0,100)+'...' : 'No description'}</p>
                                    <div class="mt-auto pt-4">
                                        <button class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 request-btn" 
                                                data-book-id="${book.user_book_id}" 
                                                data-owner-id="${book.owner_id}"
                                                data-book-title="${book.title}">
                                            Request Exchange
                                        </button>
                                    </div>
                                </div>`;
                        });
                    } else if (tabName === 'my-requests') {
                        data.forEach(req => {
                             html += `
                                <div class="bg-white rounded-lg shadow p-6 flex flex-col">
                                    <div>
                                        <h3 class="text-xl font-bold text-indigo-700 mb-2">${req.title}</h3>
                                        <p class="text-gray-700 mb-1"><span class="font-semibold">Owner:</span> ${req.owner_username}</p>
                                        <p class="text-gray-600 mb-1"><span class="font-semibold">Status:</span> 
                                            <span class="font-bold ${req.status === 'completed' ? 'text-green-600' : req.status === 'rejected' ? 'text-red-600' : 'text-yellow-600'}">
                                                ${req.status.charAt(0).toUpperCase() + req.status.slice(1)}
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-400 mt-2">Requested on: ${new Date(req.requested_at).toLocaleDateString()}</p>
                                    </div>
                                    <div class="mt-auto pt-4">
                                        ${req.status === 'pending' ? `
                                        <button class="w-full bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 cancel-request-btn" data-request-id="${req.request_id}">
                                            Cancel Request
                                        </button>` : ''}
                                    </div>
                                </div>`;
                        });
                    } else if (tabName === 'incoming') {
                        data.forEach(req => {
                            html += `
                                <div class="bg-white rounded-lg shadow p-6">
                                    <h3 class="text-xl font-bold text-indigo-700 mb-2">${req.requested_book_title}</h3>
                                    <p class="text-gray-700 mb-1"><span class="font-semibold">Requester:</span> ${req.requester_username}</p>
                                     <p class="text-gray-700 mb-1"><span class="font-semibold">Offering:</span> ${req.offered_book_title || 'Nothing'}</p>
                                     <p class="text-gray-600 mt-2 text-sm italic">"${req.message || 'No message provided.'}"</p>
                                    <div class="mt-4 pt-4 border-t">
                                        ${req.status === 'pending' ? `
                                        <div class="flex space-x-2">
                                            <button class="flex-1 bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 action-btn" data-action="accept" data-request-id="${req.request_id}">Accept</button>
                                            <button class="flex-1 bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 action-btn" data-action="reject" data-request-id="${req.request_id}">Reject</button>
                                        </div>` :
                                        `<p class="text-center font-bold ${req.status === 'completed' ? 'text-green-600' : 'text-red-600'}">
                                            Request ${req.status.charAt(0).toUpperCase() + req.status.slice(1)}
                                        </p>`
                                        }
                                    </div>
                                </div>`;
                        });
                    }
                }
            html += '</div>';
            tabContentArea.innerHTML = html;
        }

        // Handles all clicks inside the main content area
        tabContentArea.addEventListener('click', async function(event) {
            const target = event.target;
            if (target.classList.contains('request-btn')) {
                // Open modal to request an exchange
                document.getElementById('modal_user_book_id').value = target.dataset.bookId;
                document.getElementById('modal_owner_id').value = target.dataset.ownerId;
                document.getElementById('modal-book-title').innerText = `You are requesting: ${target.dataset.bookTitle}`;

                offerBookSelect.innerHTML = '<option>Loading your books...</option>';
                const response = await fetch('api/get_my_available_books.php');
                const myBooks = await response.json();
                
                offerBookSelect.innerHTML = '<option value="">Select a book to offer</option>';
                if(myBooks.length > 0) {
                    myBooks.forEach(book => {
                        offerBookSelect.innerHTML += `<option value="${book.user_book_id}">${book.title}</option>`;
                    });
                } else {
                    offerBookSelect.innerHTML = '<option value="">You have no available books to offer</option>';
                }

                modal.classList.remove('hidden');
            } else if (target.classList.contains('action-btn')) {
                // Handle Accept/Reject buttons
                handleRequestAction(target.dataset.action, target.dataset.requestId);
            } else if (target.classList.contains('cancel-request-btn')) {
                // Handle Cancel button
                handleCancelRequest(target.dataset.requestId);
            }
        });
        
        closeModalBtn.addEventListener('click', () => modal.classList.add('hidden'));

        sendRequestBtn.addEventListener('click', async function() {
            const offeredBookId = offerBookSelect.value;
            if (!offeredBookId) {
                alert('Please select a book to offer in exchange.');
                return;
            }

            const payload = {
                action: 'create',
                user_book_id: parseInt(document.getElementById('modal_user_book_id').value),
                owner_id: parseInt(document.getElementById('modal_owner_id').value),
                offered_book_id: parseInt(offeredBookId),
                message: document.getElementById('exchangeMessage').value
            };

            const response = await fetch('api/handle_exchange_request.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) {
                modal.classList.add('hidden');
                loadTabContent(currentTab);
            }
        });
        
        async function handleRequestAction(action, requestId) {
             if (!confirm(`Are you sure you want to ${action} this request?`)) return;

             const payload = { action, request_id: parseInt(requestId) };
             const response = await fetch('api/handle_exchange_request.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            alert(result.message);
            if (result.success) {
                loadTabContent(currentTab);
            }
        }

        async function handleCancelRequest(requestId) {
            if (!confirm('Are you sure you want to cancel this exchange request?')) return;
            
            const payload = { action: 'cancel', request_id: parseInt(requestId) };
            const response = await fetch('api/handle_exchange_request.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            alert(result.message);
            if(result.success) {
                loadTabContent(currentTab);
            }
        }

        // Load the initial tab content when the page loads
        loadTabContent('available');
    });
    </script>
</body>
</html>