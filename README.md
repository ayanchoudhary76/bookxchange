

# 📚 BookXchange

**BookXchange** is a full-stack web application where users can **exchange books** with each other and communicate via a **built-in messaging system**.  
Built with **PHP**, **Tailwind CSS**, and **MySQL**, and hosted locally using **XAMPP**, the platform enables book lovers to share and discover new reads effortlessly.

---

## 🚀 Features

- **User Authentication** – Register, log in, and manage profiles.
- **List Books for Exchange** – Add, update, or remove books from your collection.
- **Search & Browse** – Find books listed by other users.
- **Exchange Requests** – Send and receive book exchange requests.
- **Messaging System** – Chat directly with other users to finalize exchanges.
- **Dashboard** – View your available books, incoming requests, and ongoing exchanges.
- **Responsive UI** – Clean and modern interface built with Tailwind CSS.

---

## 🛠 Tech Stack

- **Frontend:** Tailwind CSS, HTML
- **Backend:** PHP
- **Database:** MySQL
- **Local Server:** XAMPP (Apache + MySQL)
- **APIs:** Custom PHP endpoints for data handling

---

## 📂 Project Structure

```

BookXchange/
└── BookXchangePHP/
├── database/
│   └── bookxchange.sql          # MySQL database schema
├── src/
│   ├── index.php                 # Landing page
│   ├── login.php                 # User login
│   ├── register.php              # User registration
│   ├── dashboard.php             # User dashboard
│   ├── add-book.php              # Add a new book
│   ├── my-books.php               # Manage user's books
│   ├── exchange.php              # Exchange request page
│   ├── messages.php              # Messaging interface
│   ├── remove-book.php           # Remove a book
│   ├── logout.php                 # End session
│   └── api/                       # API endpoints
│       ├── get_available_books.php
│       ├── get_my_available_books.php
│       ├── get_incoming_requests.php
│       ├── get_my_requests.php
│       ├── get_conversations.php
│       ├── get_messages.php
│       ├── send_message.php
│       ├── handle_exchange_request.php
│       └── update_book_status.php

````

---

## ⚙️ Installation & Setup

1. **Clone the Repository**
   ```bash
   git clone https://github.com/ayanchoudhary76/bookxchange.git
    ```

2. **Move Project to XAMPP htdocs**

   * Copy the `BookXchangePHP` folder into your `htdocs` directory.

3. **Import Database**

   * Open **phpMyAdmin**.
   * Create a new database named `bookxchange`.
   * Import `database/bookxchange.sql`.

4. **Configure Database Connection**

   * Update your DB credentials in the PHP config file (if applicable).

5. **Run the Project**

   * Start Apache & MySQL in XAMPP.
   * Visit:

     ```
     http://localhost/BookXchangePHP
     ```

---


## 🤝 Contributing

1. Fork the project
2. Create your feature branch (`git checkout -b feature/YourFeature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

---





If you want, I can **also include a “How it Works” flow** with diagrams showing how a book exchange happens and messages are sent. That would make your README even more impressive for GitHub recruiters.  

Do you want me to add that flow section?

