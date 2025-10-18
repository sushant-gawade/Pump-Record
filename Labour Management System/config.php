<?php
// config.php

$servername = "localhost"; // Usually 'localhost'
$username = "root";        // Your MySQL username
$password = ""; // Your MySQL password
$dbname = "labour_management_db";  // The name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Stop execution and show error if connection fails
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to UTF-8 for proper handling of all languages
$conn->set_charset("utf8mb4");
?>