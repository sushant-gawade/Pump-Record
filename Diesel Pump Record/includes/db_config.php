<?php
// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Change this
define('DB_PASSWORD', '');     // Change this
define('DB_NAME', 'diesel_db'); // *** CHANGED TO DIESEL_DB ***

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed. Error: " . $conn->connect_error);
}
?>