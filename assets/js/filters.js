document.addEventListener('DOMContentLoaded', function() {
    // Get filter elements
    const categoryFilter = document.getElementById('category-filter');
    const priorityFilter = document.getElementById('priority-filter');
    const dateFilter = document.getElementById('date-filter');
    
    // Add event listeners to all filters
    categoryFilter.addEventListener('change', applyFilters);
    priorityFilter.addEventListener('change', applyFilters);
    dateFilter.addEventListener('change', applyFilters);
    
    // Function to apply all filters
    function applyFilters() {
        const categoryValue = categoryFilter.value.toLowerCase();
        const priorityValue = priorityFilter.value.toLowerCase();
        const dateValue = dateFilter.value.toLowerCase();
        
        // Get all task cards
        const taskCards = document.querySelectorAll('.task-card');
        
        taskCards.forEach(card => {
            // Get task properties from the card
            const category = card.querySelector('.task-category').textContent.toLowerCase();
            const priority = card.querySelector('.task-priority').textContent.toLowerCase();
            const dueDate = new Date(parseDateFromText(card.querySelector('.task-due-date').textContent));
            
            // Check category match
            const categoryMatch = !categoryValue || 
                                 category.includes(categoryValue) || 
                                 mapCategoryValue(categoryValue) === category;
            
            // Check priority match
            const priorityMatch = !priorityValue || priority === priorityValue;
            
            // Check date match
            const dateMatch = !dateValue || isDateInRange(dueDate, dateValue);
            
            // Show/hide the card based on combined filters (AND logic)
            if (categoryMatch && priorityMatch && dateMatch) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update empty state if no tasks are visible
        updateEmptyState();
    }
    
    // Function to help with category mapping (dropdown value to actual category)
    function mapCategoryValue(value) {
        const mapping = {
            'assignments': 'assignment',
            'discussions': 'discussion',
            'club': 'club activity',
            'exams': 'examination'
        };
        return mapping[value] || value;
    }
    
    // Function to parse date from the text displayed in the card
    function parseDateFromText(dateText) {
        return dateText.replace('Due:', '').trim();
    }
    
    // Function to check if a date is within the selected range
    function isDateInRange(taskDate, rangeValue) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        
        const nextWeek = new Date(today);
        nextWeek.setDate(today.getDate() + 7);
        
        const nextMonth = new Date(today);
        nextMonth.setMonth(today.getMonth() + 1);
        
        switch(rangeValue) {
            case 'today':
                return taskDate.toDateString() === today.toDateString();
            case 'tomorrow':
                return taskDate.toDateString() === tomorrow.toDateString();
            case 'week':
                return taskDate >= today && taskDate < nextWeek;
            case 'month':
                return taskDate >= today && taskDate < nextMonth;
            default:
                return true; // 'all dates' or empty selection
        }
    }
    
    // Function to update empty state message
    function updateEmptyState() {
        const taskCards = document.querySelectorAll('.task-card');
        const visibleCards = Array.from(taskCards).filter(card => card.style.display !== 'none');
        const taskList = document.querySelector('.task-list');
        const contentDiv = document.querySelector('.content');
        
        // First, check if we need to show or hide the empty state
        if (visibleCards.length === 0) {
            // Hide the task list entirely
            if (taskList) taskList.style.display = 'none';
            
            // Look for existing empty state or create one
            let emptyState = document.querySelector('.empty-state');
            
            if (!emptyState) {
                // Create empty state that matches your existing design
                emptyState = document.createElement('div');
                emptyState.className = 'empty-state';
                emptyState.innerHTML = `
                    <div class="empty-state-icon">
                        <i class="fas fa-filter"></i>
                    </div>
                    <h3 class="empty-state-title">No matching tasks</h3>
                    <p class="empty-state-message">Try changing your filters to see more tasks.</p>
                `;
                
                // Append to content div, not task list
                if (contentDiv) {
                    contentDiv.appendChild(emptyState);
                }
            } else {
                // Update existing empty state for filter context
                const title = emptyState.querySelector('.empty-state-title');
                const message = emptyState.querySelector('.empty-state-message');
                const icon = emptyState.querySelector('.empty-state-icon i');
                
                if (title) title.textContent = 'No matching tasks';
                if (message) message.textContent = 'Try changing your filters to see more tasks.';
                if (icon) {
                    icon.className = ''; // Clear existing classes
                    icon.classList.add('fas', 'fa-filter');
                }
                
                emptyState.style.display = 'flex';
            }
        } else {
            // Show the task list and hide any empty state
            if (taskList) taskList.style.display = 'grid';
            
            const emptyState = document.querySelector('.empty-state');
            if (emptyState) {
                emptyState.style.display = 'none';
            }
        }
    }
});