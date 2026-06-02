document.addEventListener('DOMContentLoaded', function () {
    const searchInput    = document.getElementById('searchInput');
    const priorityFilter = document.getElementById('priorityFilter');
    const statusFilter   = document.getElementById('statusFilter');

    if (!searchInput) return; 

    const notifiedTasks = new Set();
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
                requireInteraction: true   
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

    function checkOverdue() {
        const now   = new Date();
        const tasks = document.querySelectorAll('.task');

        tasks.forEach(task => {
            // skip completed tasks
            if (task.getAttribute('data-status') === 'completed') return;

            const dueDateEl = task.querySelector('.due-date');
            if (!dueDateEl) return;

            const dateStr  = dueDateEl.getAttribute('data-due-date'); 
            const timeStr  = dueDateEl.getAttribute('data-due-time'); 
            const taskId   = task.getAttribute('data-id');
            const taskTitle = task.querySelector('h3')?.textContent.trim() || 'A task';

            if (!dateStr || !timeStr) return; 

            const dueDatetime = new Date(`${dateStr}T${timeStr}:00`);
            if (isNaN(dueDatetime)) return;

            const diffMs      = dueDatetime - now;          
            const diffMinutes = diffMs / 60000;            

         
            if (diffMs < 0) {
                task.classList.add('overdue');
                dueDateEl.style.color      = '#e74c3c';
                dueDateEl.style.fontWeight = 'bold';
            } else {
                task.classList.remove('overdue');
                dueDateEl.style.color      = '';
                dueDateEl.style.fontWeight = '';
            }

           
            if (diffMinutes > 0 && diffMinutes <= 10 && !notifiedTasks.has(taskId)) {
                notifiedTasks.add(taskId);
                sendNotification(taskTitle);
            }

            if (diffMinutes > 10 && notifiedTasks.has(taskId)) {
                notifiedTasks.delete(taskId);
            }

            if (diffMinutes > -1 && diffMinutes <= 0 && !dueNowTasks.has(taskId)) {
                dueNowTasks.add(taskId);
                sendDueNowNotification(taskTitle);
            }

            if (diffMinutes > 0 && dueNowTasks.has(taskId)) {
                dueNowTasks.delete(taskId);
            }
        });
    }

    searchInput.addEventListener('input', filterTasks);
    priorityFilter.addEventListener('change', filterTasks);
    statusFilter.addEventListener('change', filterTasks);

    filterTasks();

    setInterval(checkOverdue, 60000);
});
