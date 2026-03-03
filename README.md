# TaskHub — To-Do List Web Application with REST API

TaskHub is a task management web application built with PHP and MySQL, featuring a full REST API layer with JWT authentication. Originally developed as a traditional server-rendered PHP app, it was extended with a RESTful API to demonstrate modern web development concepts including stateless authentication, API design, and frontend-backend separation.

> This application is fictitious and is part of the IBM4202 course at INTI International University.

---

## Features

### Web Application
- User registration and login with secure password hashing
- Google OAuth 2.0 login integration
- Task CRUD (Create, Read, Update, Delete) with modal-based UI
- Task categorization (Assignment, Discussion, Club Activity, Examination)
- Priority levels (High, Medium, Low) and status tracking (Pending, On-going, Completed)
- Task filtering by category, priority, and due date
- Task archiving for completed tasks
- Automatic deletion of completed tasks after 7 days with advance notifications
- Daily task reminders with notification system
- Profile management with photo upload, name/email/department editing, and password changes
- Responsive dark-themed UI

### REST API
- 11 endpoints covering authentication, tasks, notifications, and profile management
- JWT (JSON Web Token) based stateless authentication
- JSON request/response format
- Proper HTTP status codes and error handling
- Tested with Postman

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8.2 |
| Database | MySQL (via PDO) |
| Server | Apache (XAMPP) |
| Authentication | Sessions (web) + JWT (API) |
| JWT Library | firebase/php-jwt (Composer) |
| Frontend | HTML, CSS, JavaScript (Vanilla) |
| Icons | Font Awesome 6.4 |
| API Testing | Postman |

---

## Project Structure

```
ToDoListApp/
├── api/                        # REST API layer
│   ├── .htaccess               # URL rewriting for clean API routes
│   ├── index.php               # Front controller / router
│   ├── auth/
│   │   ├── login.php           # POST /api/auth/login
│   │   └── register.php        # POST /api/auth/register
│   ├── config/
│   │   ├── database.php        # Database connection (gitignored)
│   │   ├── database_example.php
│   │   ├── jwt.php             # JWT secret and config (gitignored)
│   │   └── jwt_example.php
│   ├── middleware/
│   │   └── auth.php            # JWT token verification middleware
│   ├── tasks/
│   │   ├── index.php           # GET /api/tasks
│   │   ├── show.php            # GET /api/tasks/{id}
│   │   ├── create.php          # POST /api/tasks
│   │   ├── update.php          # PUT /api/tasks/{id}
│   │   └── delete.php          # DELETE /api/tasks/{id}
│   ├── notifications/
│   │   ├── index.php           # GET /api/notifications
│   │   └── update.php          # PUT /api/notifications/{id}
│   └── profile/
│       ├── update.php          # PUT /api/profile
│       └── picture.php         # POST /api/profile/picture
├── actions/                    # Original PHP form handlers
│   ├── add_task.php
│   ├── update_task.php
│   ├── delete_task.php
│   ├── archive_task.php
│   ├── get_task.php
│   ├── profile-update.php
│   ├── upload_profile_image.php
│   ├── notification-actions.php
│   ├── process-register.php
│   ├── google-login.php
│   └── google-callback.php
├── assets/
│   ├── css/
│   │   ├── dashboardstyle.css
│   │   └── loginstyle.css
│   └── js/
│       ├── api-helper.js       # Shared API utility (token management, requests)
│       ├── addtask.js          # Task CRUD via API
│       ├── viewtask.js         # View task details via API
│       ├── profilescript.js    # Profile updates via API
│       ├── loginscript.js      # Login via API + session
│       ├── registerscript.js   # Registration via API
│       ├── notificationscript.js # Notification actions via API
│       ├── logoutscript.js     # Logout with token cleanup
│       ├── alerts.js           # Shared alert/dialog utilities
│       └── filters.js          # Client-side task filtering
├── components/
│   ├── sidebar.php
│   ├── task-card.php
│   ├── task-modal.php
│   └── confirmation-dialog.php
├── includes/
│   ├── PDOconn.php             # Database connection (gitignored)
│   ├── PDOconn_example.php
│   ├── config.php              # Google OAuth credentials (gitignored)
│   ├── config_example.php
│   ├── auth-check.php
│   ├── login.php
│   ├── logout.php
│   ├── user-functions.php
│   └── notification-functions.php
├── public/
│   ├── login-page.php
│   ├── register-page.php
│   ├── dashboard-page.php
│   ├── pending-tasks-page.php
│   ├── ongoing-tasks-page.php
│   ├── completed-tasks-page.php
│   ├── archived-tasks-page.php
│   ├── notifications-page.php
│   └── profile-page.php
├── uploads/
│   └── profile_pictures/
├── vendor/                     # Composer dependencies (gitignored)
├── composer.json
├── composer.lock
└── .gitignore
```

---

## API Endpoints

All endpoints return JSON. Protected routes require a Bearer token in the Authorization header.

### Authentication
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/auth/register` | Create a new account | No |
| POST | `/api/auth/login` | Login and receive JWT token | No |

### Tasks
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/tasks` | List all tasks (supports `?status=`, `?category=`, `?priority=` filters) | Yes |
| GET | `/api/tasks/{id}` | Get a single task | Yes |
| POST | `/api/tasks` | Create a new task | Yes |
| PUT | `/api/tasks/{id}` | Update a task | Yes |
| DELETE | `/api/tasks/{id}` | Delete a task | Yes |

### Notifications
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/notifications` | List all notifications with unread count | Yes |
| PUT | `/api/notifications/{id}` | Mark a notification as read | Yes |

### Profile
| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| PUT | `/api/profile` | Update profile info or password (via `action` field) | Yes |
| POST | `/api/profile/picture` | Upload profile picture (multipart/form-data) | Yes |

---

## Setup Instructions

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8.x)
- Composer
- Git

### Installation

1. **Clone the repository** into your XAMPP htdocs directory:
   ```bash
   git clone https://github.com/yourusername/ToDoListApp.git
   cd ToDoListApp
   ```

2. **Install Composer dependencies** (for JWT library):
   ```bash
   composer install
   ```

3. **Create the database.** Open phpMyAdmin and create a database, then import the schema or create the following tables:

   **users**
   ```sql
   CREATE TABLE users (
       user_id INT AUTO_INCREMENT PRIMARY KEY,
       NAME VARCHAR(255) NOT NULL,
       email VARCHAR(255) UNIQUE NOT NULL,
       PASSWORD VARCHAR(255) NOT NULL,
       department VARCHAR(255) DEFAULT NULL,
       profile_picture VARCHAR(255) DEFAULT NULL,
       created_at DATETIME DEFAULT CURRENT_TIMESTAMP
   );
   ```

   **tasks**
   ```sql
   CREATE TABLE tasks (
       task_id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       title VARCHAR(255) NOT NULL,
       description TEXT DEFAULT NULL,
       due_date DATE NOT NULL,
       category ENUM('Assignment','Discussion','Club Activity','Examination') DEFAULT 'Assignment',
       priority ENUM('Low','Medium','High') DEFAULT 'Medium',
       status ENUM('Pending','On-going','Completed') DEFAULT 'Pending',
       archived TINYINT(1) DEFAULT 0,
       reminder_enabled TINYINT(1) DEFAULT 0,
       created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       last_updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       completion_date DATETIME DEFAULT NULL,
       FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
   );
   ```

   **notifications**
   ```sql
   CREATE TABLE notifications (
       notification_id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       task_id INT DEFAULT NULL,
       notify_time DATETIME DEFAULT CURRENT_TIMESTAMP,
       message TEXT NOT NULL,
       status ENUM('Unread','Read') DEFAULT 'Unread',
       created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
       FOREIGN KEY (task_id) REFERENCES tasks(task_id) ON DELETE SET NULL
   );
   ```

4. **Configure credentials.** Copy the example files and fill in your values:
   ```bash
   cp includes/PDOconn_example.php includes/PDOconn.php
   cp includes/config_example.php includes/config.php
   cp api/config/database_example.php api/config/database.php
   cp api/config/jwt_example.php api/config/jwt.php
   ```

   Edit each file with your database credentials and a secure JWT secret.

5. **Start XAMPP** (Apache and MySQL).

6. **Access the application** at:
   ```
   http://localhost/project/ToDoListApp/public/login-page.php
   ```

---

## Architecture

TaskHub uses a dual-authentication approach:

- **Web pages** use PHP sessions. When a user logs in through the browser, a session is created so PHP pages can verify access via `$_SESSION['user_id']`.
- **API calls** use JWT tokens. When JavaScript makes API requests (adding tasks, updating profile, etc.), it includes the JWT token in the Authorization header.

Both systems share the same MySQL database. The web app and API are two doors into the same building — sessions for page-level access control, JWT for data operations via JavaScript.

```
Browser (User)
    │
    ├─── PHP Pages ──── Sessions ──── Database
    │                                    │
    └─── JavaScript ──── JWT API ────────┘
```

---

## Testing with Postman

1. Send `POST /api/auth/login` with email and password to get a JWT token.
2. In Postman, go to the Authorization tab, select "Bearer Token", and paste the token.
3. Test any protected endpoint — the token is included automatically.

Visit `/api/` in your browser to see a list of all available endpoints.

---

## License

This project was created for educational purposes as part of the IBM4202 course at INTI International University.