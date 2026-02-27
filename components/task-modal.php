<div id="addTaskModal" class="modal">
        <div class="modal-content">
            <div class="modal-header"> 
                <h2></h2>
                <span class="close-modal">&times;</span>
            </div>

            <div class="modal-body">
                <form id="addTaskForm" method="post" action="../actions/add_task.php">
                    <div class="form-group">
                        <label for="taskTitle">Title</label>
                        <input type="text" id="taskTitle" name="title" required>
                    </div>

                    <div class="form-group">
                        <label for="taskDescription">Description</label>
                        <textarea name="description" id="taskDescription" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="taskCategory">Category</label>
                        <select name="category" id="taskCategory">
                            <option value="Assignment">Assignment</option>
                            <option value="Discussion">Discussion</option>
                            <option value="Club Activity">Club Activity</option>
                            <option value="Examination">Examination</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="taskDueDate">Due Date</label>
                        <input type="date" id="taskDueDate" name="due_date" required>
                    </div>

                    <div class="form-group">
                        <label for="taskPriority">Priority</label>
                        <select name="priority" id="taskPriority">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="taskStatus">Status</label>
                        <select name="status" id="taskStatus">
                            <option value="Pending" selected>Pending</option>
                            <option value="On-going">On-going</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>

                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="enableReminder" name="enable_reminder" value="1">
                            <span>Send me daily reminder about this task (8 AM)</span>
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="cancelTask">Cancel</button>
                        <button type="submit" class="btn-submit">Add Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>