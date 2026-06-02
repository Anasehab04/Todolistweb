<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_task'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];
        $due_time = !empty($_POST['due_time']) ? $_POST['due_time'] : null;
        $priority = $_POST['priority'];
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, title, description, due_date, due_time, priority) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $description, $due_date, $due_time, $priority]);
    }

    if (isset($_POST['complete_task'])) {
        $task_id = $_POST['task_id'];
        $stmt = $pdo->prepare("UPDATE tasks SET status = 'completed' WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $_SESSION['user_id']]);
    }

    if (isset($_POST['delete_task'])) {
        $task_id = $_POST['task_id'];
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$task_id, $_SESSION['user_id']]);
    }

    if (isset($_POST['update_task'])) {
        $task_id = $_POST['task_id'];
        $title = $_POST['title'];
        $description = $_POST['description'];
        $due_date = $_POST['due_date'];
        $due_time = !empty($_POST['due_time']) ? $_POST['due_time'] : null;
        $priority = $_POST['priority'];
        $stmt = $pdo->prepare("UPDATE tasks SET title = ?, description = ?, due_date = ?, due_time = ?, priority = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$title, $description, $due_date, $due_time, $priority, $task_id, $_SESSION['user_id']]);
    }
}

$edit_task = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['edit'], $_SESSION['user_id']]);
    $edit_task = $stmt->fetch();
}

$stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    COUNT(*) as total_count
    FROM tasks WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

$stmt = $pdo->prepare("SELECT * FROM tasks WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$tasks = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todo List Project — My Tasks</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="script.js" defer></script>
</head>
<body>
<div class="container">

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="header-logo">📝</div>
            <h2>Hello, <span><?php echo htmlspecialchars($_SESSION['username']); ?></span> 👋</h2>
        </div>
        <form method="POST" action="logout.php" style="display:inline;">
            <button type="submit" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </button>
        </form>
    </div>

    <div class="dashboard">
        <div class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <h3>Pending</h3>
                <div class="stat-number"><?php echo (int)$stats['pending_count']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <h3>Completed</h3>
                <div class="stat-number"><?php echo (int)$stats['completed_count']; ?></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <h3>Total</h3>
                <div class="stat-number"><?php echo (int)$stats['total_count']; ?></div>
            </div>
        </div>
    </div>

    <div class="search-filter">
        <input type="text" id="searchInput" placeholder="🔍  Search tasks...">
        <select id="priorityFilter">
            <option value="">All Priorities</option>
            <option value="high">🔴 High</option>
            <option value="medium">🟡 Medium</option>
            <option value="low">🟢 Low</option>
        </select>
        <select id="statusFilter">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
        </select>
    </div>

    <form method="POST" class="task-form">
        <div class="form-section-label">
            <?php echo $edit_task ? '✏️ Edit Task' : '＋ New Task'; ?>
        </div>

        <?php if ($edit_task): ?>
            <input type="hidden" name="task_id" value="<?php echo $edit_task['id']; ?>">
        <?php endif; ?>

        <input type="text" name="title" placeholder="Task title"
               value="<?php echo $edit_task ? htmlspecialchars($edit_task['title']) : ''; ?>" required>

        <textarea name="description" placeholder="Description (optional)"><?php echo $edit_task ? htmlspecialchars($edit_task['description']) : ''; ?></textarea>

        <div class="form-row">
            <input type="date" name="due_date"
                   value="<?php echo $edit_task ? $edit_task['due_date'] : ''; ?>" required>
            <input type="time" name="due_time"
                   value="<?php echo ($edit_task && !empty($edit_task['due_time'])) ? substr($edit_task['due_time'], 0, 5) : ''; ?>"
                   placeholder="Due time (optional)">
            <select name="priority" required>
                <option value="high"   <?php echo ($edit_task && $edit_task['priority']=='high')   ? 'selected' : ''; ?>>🔴 High Priority</option>
                <option value="medium" <?php echo (!$edit_task || $edit_task['priority']=='medium') ? 'selected' : ''; ?>>🟡 Medium Priority</option>
                <option value="low"    <?php echo ($edit_task && $edit_task['priority']=='low')    ? 'selected' : ''; ?>>🟢 Low Priority</option>
            </select>
        </div>

        <div class="form-actions">
            <?php if ($edit_task): ?>
                <button type="submit" name="update_task">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
                <a href="index.php" class="button">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </a>
            <?php else: ?>
                <button type="submit" name="add_task">
                    <i class="fa-solid fa-plus"></i> Add Task
                </button>
            <?php endif; ?>
        </div>
    </form>

    <div class="tasks-header">
        <h3>📌 Your Tasks</h3>
    </div>

    <div class="tasks">
        <?php if (empty($tasks)): ?>
            <div class="empty-state">
                <span class="empty-icon">🗂️</span>
                <p>No tasks yet. Add your first task above!</p>
            </div>
        <?php endif; ?>

        <?php foreach ($tasks as $task): ?>
            <div class="task <?php echo $task['status'] == 'completed' ? 'completed' : ''; ?> priority-<?php echo $task['priority']; ?>"
                 data-id="<?php echo $task['id']; ?>"
                 data-priority="<?php echo $task['priority']; ?>"
                 data-status="<?php echo $task['status']; ?>">

                <h3><?php echo htmlspecialchars($task['title']); ?></h3>

                <?php if ($task['description']): ?>
                    <p><?php echo htmlspecialchars($task['description']); ?></p>
                <?php endif; ?>

                <div class="task-meta">
                    <span class="due-date"
                          data-due-date="<?php echo $task['due_date']; ?>"
                          data-due-time="<?php echo !empty($task['due_time']) ? substr($task['due_time'], 0, 5) : ''; ?>">
                        <i class="fa-regular fa-calendar"></i>
                        <?php echo date('M d, Y', strtotime($task['due_date'])); ?>
                        <?php if (!empty($task['due_time'])): ?>
                            <i class="fa-regular fa-clock" style="margin-left:6px;"></i>
                            <?php echo date('h:i A', strtotime($task['due_time'])); ?>
                        <?php endif; ?>
                    </span>
                    <span class="priority">
                        <?php
                            $icons = ['high'=>'🔴','medium'=>'🟡','low'=>'🟢'];
                            echo ($icons[$task['priority']] ?? '') . ' ' . ucfirst($task['priority']);
                        ?>
                    </span>
                </div>

                <div class="task-actions">
                    <?php if ($task['status'] == 'pending'): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                            <button type="submit" name="complete_task" class="btn-complete">
                                <i class="fa-solid fa-check"></i> Complete
                            </button>
                        </form>
                        <button type="button" class="edit-btn"
                                onclick="window.location.href='?edit=<?php echo $task['id']; ?>'">
                            <i class="fa-solid fa-pen"></i> Edit
                        </button>
                    <?php endif; ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                        <button type="submit" name="delete_task" class="delete-btn"
                                onclick="return confirm('Delete this task?')">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
</body>
</html>
