document.addEventListener('DOMContentLoaded', function () {
    const searchInput    = document.getElementById('searchInput');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter   = document.getElementById('statusFilter');

    if (!searchInput) return; // not on the main page

    // ── Notification permission ───────────────────────────────────────────────
    // Track which task IDs have already fired a 10-min notification this session
    const notifiedTasks = new Set();
    // Track which task IDs have already fired a "due now" notification this session
    const dueNowTasks = new Set();

    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    function sendNotification(taskTitle) {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            new Notification('⏰ Task Due Soon!', {
                body: `"${taskTitle}" is due in 10 minutes.`,
                icon: 'https://cdn-icons-png.flaticon.com/512/1827/1827392.png',
                requireInteraction: true   // stays on screen until dismissed
            });
        }
    }

    function sendDueNowNotification(taskTitle) {
        if (!('Notification' in window)) return;

        if (Notification.permission === 'granted') {
            new Notification('🔔 Task Due Now!', {
                body: `"${taskTitle}" is due right now!`,
                icon: 'https://cdn-icons-png.flaticon.com/512/1827/1827392.png',
                requireInteraction: true
            });
        }
    }

    // ── Filter ────────────────────────────────────────────────────────────────
    function filterTasks() {
        const searchTerm    = searchInput.value.toLowerCase();
        const priorityValue = priorityFilter.value;
        const statusValue   = statusFilter.value;
        const tasks         = document.querySelectorAll('.task');

        tasks.forEach(task => {
            const title       = task.querySelector('h3')?.textContent.toLowerCase() || '';
            const description = task.querySelector('p')?.textContent.toLowerCase()  || '';
            const priority    = task.getAttribute('data-priority') || '';
            const status      = task.getAttribute('data-status')   || 'pending';

            const matchesSearch   = !searchTerm   || title.includes(searchTerm) || description.includes(searchTerm);
            const matchesPriority = !priorityValue || priority === priorityValue;
            const matchesStatus   = !statusValue   || status   === statusValue;

            task.style.display = (matchesSearch && matchesPriority && matchesStatus) ? '' : 'none';
        });

        checkOverdue();
    }

    // ── Overdue + 10-min notification ─────────────────────────────────────────
    function checkOverdue() {
        const now   = new Date();
        const tasks = document.querySelectorAll('.task');

        tasks.forEach(task => {
            // skip completed tasks
            if (task.getAttribute('data-status') === 'completed') return;

            const dueDateEl = task.querySelector('.due-date');
            if (!dueDateEl) return;

            const dateStr  = dueDateEl.getAttribute('data-due-date'); // "2025-06-01"
            const timeStr  = dueDateEl.getAttribute('data-due-time'); // "14:30" or ""
            const taskId   = task.getAttribute('data-id');
            const taskTitle = task.querySelector('h3')?.textContent.trim() || 'A task';

            if (!dateStr || !timeStr) return; // no time set → skip notification

            const dueDatetime = new Date(`${dateStr}T${timeStr}:00`);
            if (isNaN(dueDatetime)) return;

            const diffMs      = dueDatetime - now;          // ms until due
            const diffMinutes = diffMs / 60000;             // convert to minutes

            // ── Turn red if overdue ───────────────────────────────────────────
            if (diffMs < 0) {
                task.classList.add('overdue');
                dueDateEl.style.color      = '#e74c3c';
                dueDateEl.style.fontWeight = 'bold';
            } else {
                task.classList.remove('overdue');
                dueDateEl.style.color      = '';
                dueDateEl.style.fontWeight = '';
            }

            // ── Fire notification if within 10-min window ────────────────────
            // Window: due time is between 0 and 10 minutes away
            // notifiedTasks prevents sending the same notification repeatedly
            if (diffMinutes > 0 && diffMinutes <= 10 && !notifiedTasks.has(taskId)) {
                notifiedTasks.add(taskId);
                sendNotification(taskTitle);
            }

            // Reset notification if task was edited to a future time beyond 10 min
            if (diffMinutes > 10 && notifiedTasks.has(taskId)) {
                notifiedTasks.delete(taskId);
            }

            // ── Fire "due now" notification when task time arrives ────────────
            // Window: task just became due (within the last 1 minute)
            if (diffMinutes > -1 && diffMinutes <= 0 && !dueNowTasks.has(taskId)) {
                dueNowTasks.add(taskId);
                sendDueNowNotification(taskTitle);
            }

            // Reset due-now if task was rescheduled to the future
            if (diffMinutes > 0 && dueNowTasks.has(taskId)) {
                dueNowTasks.delete(taskId);
            }
        });
    }

    // ── Event listeners ───────────────────────────────────────────────────────
    searchInput.addEventListener('input', filterTasks);
    priorityFilter.addEventListener('change', filterTasks);
    statusFilter.addEventListener('change', filterTasks);

    filterTasks();

    // Re-check every minute — catches the 10-min window and updates overdue colors
    setInterval(checkOverdue, 60000);
});
