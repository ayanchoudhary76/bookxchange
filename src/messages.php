<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("location: index.php");
    exit();
}
// Store session ID in a JavaScript-accessible way
$current_user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Messages - BookXchange</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Simple scrollbar styling */
        .chat-messages::-webkit-scrollbar { width: 5px; }
        .chat-messages::-webkit-scrollbar-track { background: #f1f1f1; }
        .chat-messages::-webkit-scrollbar-thumb { background: #888; border-radius: 5px; }
        .chat-messages::-webkit-scrollbar-thumb:hover { background: #555; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">

    <nav class="bg-gray-100 shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 flex justify-between h-16 items-center">
            <h1 class="text-2xl font-bold text-indigo-600">BookXchange</h1>
            <div class="space-x-4">
                <a href="dashboard.php" class="text-gray-700 hover:text-indigo-600">Dashboard</a>
                <a href="my-books.php" class="text-gray-700 hover:text-indigo-600">My Books</a>
                <a href="exchange.php" class="text-gray-700 hover:text-indigo-600">Exchange</a>
                <a href="logout.php" class="text-red-600 hover:text-red-800">Logout</a>
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto py-10 px-4">
        <h2 class="text-3xl font-bold text-gray-900 mb-6">Messages</h2>
        
        <div class="flex h-[70vh] bg-white rounded-lg shadow-md">
            <div id="conversation-list" class="w-1/3 border-r border-gray-200 overflow-y-auto">
                </div>

            <div id="chat-window" class="w-2/3 flex flex-col hidden">
                <div id="chat-placeholder" class="flex-grow flex items-center justify-center">
                    <p class="text-gray-500">Select a conversation to start chatting.</p>
                </div>
            </div>
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const conversationList = document.getElementById('conversation-list');
    const chatWindow = document.getElementById('chat-window');
    const chatPlaceholder = document.getElementById('chat-placeholder');
    const currentUserId = <?php echo $current_user_id; ?>;
    let activeChatInterval = null; // To hold the interval for polling new messages

    // Function to fetch all conversations
    async function loadConversations() {
        try {
            const response = await fetch('api/get_conversations.php');
            const conversations = await response.json();

            if (conversations.error) {
                conversationList.innerHTML = `<p class="p-4 text-red-500">${conversations.error}</p>`;
                return;
            }

            if (conversations.length === 0) {
                conversationList.innerHTML = `<p class="p-4 text-gray-500">No conversations yet.</p>`;
                return;
            }
            
            conversationList.innerHTML = ''; // Clear previous list
            conversations.forEach(convo => {
                const convoElement = document.createElement('div');
                convoElement.className = 'p-4 border-b border-gray-200 cursor-pointer hover:bg-gray-50';
                convoElement.dataset.exchangeId = convo.exchange_request_id;
                convoElement.dataset.otherUserId = convo.other_user_id;
                convoElement.dataset.otherUserName = convo.other_user;

                convoElement.innerHTML = `
                    <div class="flex justify-between">
                        <h3 class="font-bold text-gray-800">${convo.other_user}</h3>
                        ${convo.unread_count > 0 ? `<span class="bg-indigo-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">${convo.unread_count}</span>` : ''}
                    </div>
                    <p class="text-sm text-gray-600 truncate">RE: ${convo.book_title}</p>
                    <p class="text-sm text-gray-400 italic truncate">${convo.last_message || 'No messages yet.'}</p>
                `;
                conversationList.appendChild(convoElement);
            });

        } catch (error) {
            console.error("Failed to load conversations:", error);
            conversationList.innerHTML = `<p class="p-4 text-red-500">Could not load conversations.</p>`;
        }
    }

    // Function to load messages for a specific conversation
    async function loadMessages(exchangeId, otherUserId, otherUserName) {
        // Stop any previous chat polling
        if (activeChatInterval) {
            clearInterval(activeChatInterval);
        }
        
        chatPlaceholder.classList.add('hidden');
        chatWindow.classList.remove('hidden');

        // Render the chat window structure
        chatWindow.innerHTML = `
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h3 class="font-bold text-xl text-gray-800">Chat with ${otherUserName}</h3>
            </div>
            <div id="chat-messages" class="flex-grow p-4 overflow-y-auto chat-messages space-y-4">
                <p class="text-center">Loading messages...</p>
            </div>
            <div class="p-4 bg-gray-100">
                <form id="message-form" class="flex space-x-2">
                    <input type="text" id="message-input" class="flex-grow border rounded-full px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Type a message..." autocomplete="off">
                    <button type="submit" class="bg-indigo-600 text-white rounded-full px-6 py-2 hover:bg-indigo-700">Send</button>
                </form>
            </div>
        `;

        await fetchAndRenderMessages(exchangeId);

        // Start polling for new messages every 3 seconds
        activeChatInterval = setInterval(() => fetchAndRenderMessages(exchangeId), 3000);
        
        // Handle form submission
        const messageForm = document.getElementById('message-form');
        messageForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const messageInput = document.getElementById('message-input');
            const content = messageInput.value.trim();
            if (!content) return;

            await fetch('api/send_message.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    receiver_id: otherUserId,
                    exchange_request_id: exchangeId,
                    content: content
                })
            });
            messageInput.value = '';
            await fetchAndRenderMessages(exchangeId); // Fetch immediately after sending
        });
    }

    async function fetchAndRenderMessages(exchangeId) {
        const messagesContainer = document.getElementById('chat-messages');
        const response = await fetch(`api/get_messages.php?exchange_id=${exchangeId}`);
        const messages = await response.json();
        
        messagesContainer.innerHTML = '';
        if (messages.length === 0) {
            messagesContainer.innerHTML = '<p class="text-center text-gray-400">No messages yet. Say hi!</p>';
        }

        messages.forEach(msg => {
            const isCurrentUser = msg.sender_id == currentUserId;
            const messageElement = document.createElement('div');
            messageElement.className = `flex ${isCurrentUser ? 'justify-end' : 'justify-start'}`;
            messageElement.innerHTML = `
                <div class="max-w-xs lg:max-w-md px-4 py-2 rounded-2xl ${isCurrentUser ? 'bg-indigo-500 text-white' : 'bg-gray-200 text-gray-800'}">
                    <p>${msg.content}</p>
                </div>
            `;
            messagesContainer.appendChild(messageElement);
        });
        // Scroll to the bottom
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Event listener for clicking on a conversation
    conversationList.addEventListener('click', function(e) {
        const convoElement = e.target.closest('[data-exchange-id]');
        if (convoElement) {
            // Highlight the selected conversation
            Array.from(conversationList.children).forEach(child => child.classList.remove('bg-indigo-100'));
            convoElement.classList.add('bg-indigo-100');
            
            loadMessages(
                convoElement.dataset.exchangeId,
                convoElement.dataset.otherUserId,
                convoElement.dataset.otherUserName
            );
            // After clicking, refresh the conversation list to update unread counts
            setTimeout(loadConversations, 500);
        }
    });

    // Initial load
    loadConversations();
});
</script>

</body>
</html>