<?php
// ============================================================
// TaskHub API - Database Connection
// ============================================================
// 
// WHY A SEPARATE DB FILE?
// Your existing app uses includes/PDOconn.php, which works fine.
// But the API lives in a different folder, so the relative path
// to PDOconn.php would be fragile (../../includes/PDOconn.php).
// 
// Instead, we create a clean function that returns a PDO connection.
// Any API file can call getDBConnection() without worrying about paths.
// ============================================================

function getDBConnection() {
    // These should match your existing PDOconn.php values
    $host = 'localhost';
    $dbname = 'databasename';    // Your database name
    $username = 'username';          // Default XAMPP username
    $password = 'yourpassword';              // Default XAMPP password (empty)

    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password
        );

        // These settings make PDO behave better for an API:

        // ERRMODE_EXCEPTION: Throw exceptions on DB errors instead of 
        // silently failing. This way we can catch them and return 
        // proper JSON error responses.
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // FETCH_ASSOC: When fetching results, return arrays with 
        // column names as keys (e.g., $row['title']) instead of 
        // numbered indexes (e.g., $row[0]).
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;

    } catch (PDOException $e) {
        // If the database connection fails, return a JSON error.
        // In a real production API, you wouldn't expose the actual 
        // error message — but for development/learning, it's helpful.
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]);
        exit();
    }
}
