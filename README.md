# 📝 Todo List Project

A web-based task management application where users can register, log in, and manage their personal tasks. The app is hosted live on InfinityFree.

🌐 **Live Website:** [todolistweb.infinityfreeapp.com](https://todolistweb.infinityfreeapp.com)

---

## 🔐 User Authentication

- **Register** – Create a new account with a username, email, and password. The password must meet strength requirements: at least 8 characters, one uppercase letter, one number, and one special symbol.
- **Login** – Sign in securely with your email and password.
- **Logout** – End your session and return to the login page.
- **Forgot Password** – Reset your password through a 2-step flow: verify your email, then set a new password.

---

## ✅ Task Management

Users can fully manage their tasks with the following actions:

- **Add Task** – Create a task with a title, optional description, due date, optional due time, and priority level (High, Medium, or Low).
- **Edit Task** – Update any task's details at any time.
- **Complete Task** – Mark a pending task as done.
- **Delete Task** – Permanently remove a task with a confirmation prompt.

---

## 📊 Dashboard

At the top of the main page, a live stats panel shows:

- Number of **Pending** tasks
- Number of **Completed** tasks
- **Total** task count

---

## 🔍 Search & Filter

Users can instantly narrow down their task list using:

- **Search bar** – Filter tasks by title or description keyword in real time.
- **Priority filter** – Show only High, Medium, or Low priority tasks.
- **Status filter** – Show only Pending or Completed tasks.

---

## 🔔 Smart Notifications

The app sends browser notifications to keep users on track:

- Requests notification permission when the page loads.
- Sends a **"Due in 10 minutes"** alert before a task's deadline.
- Sends a **"Due Now"** alert exactly when the deadline arrives.
- Overdue tasks are automatically **highlighted in red** on the task list.

---

## 🛠️ Tech Stack

| Layer    | Technology               |
|----------|--------------------------|
| Backend  | PHP with PDO             |
| Database | MySQL                    |
| Frontend | HTML, CSS, JavaScript    |
| Icons    | Font Awesome 6           |
| Hosting  | InfinityFree             |

---

## 🔒 Security

- Passwords are securely **hashed** before being stored — never saved as plain text.
- All database queries use **prepared statements** to prevent SQL injection.
- All displayed content is **escaped** to prevent XSS attacks.
- Every task operation is tied to the logged-in user — users cannot view or modify each other's data.
- 
