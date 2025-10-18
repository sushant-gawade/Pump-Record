<?php
// Database credentials
// -------------------------------------------------------------------

// 1. DB_SERVER: Usually 'localhost' when running XAMPP/WAMP locally
define('DB_SERVER', 'localhost');

// 2. DB_USERNAME: Default is 'root' for XAMPP/WAMP
define('DB_USERNAME', 'root'); 

// 3. DB_PASSWORD: Default is empty ('') for XAMPP/WAMP on Windows/Mac
//    If you set a password, enter it here.
define('DB_PASSWORD', '');     

// 4. DB_NAME: Must match the database you created in Step 1 of the guide
define('DB_NAME', 'petrol_db'); 

// -------------------------------------------------------------------

// Attempt to connect to MySQL database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // This message is what your index.php is currently echoing.
    // It helps debug by displaying the specific connection error.
    die("Database connection failed. Please check includes/db_config.php. Error: " . $conn->connect_error);
}

// Optional: Set charset to UTF8
$conn->set_charset("utf8");

?>