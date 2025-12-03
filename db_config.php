<?php
// db_config.php - Database Connection Configuration

$servername = "localhost";
$username = "root";
$password = ""; // Password is blank for WAMP default setup
$dbname = "myproj_db";

// Create connection object
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection for errors
if ($conn->connect_error) {
    // Stop the script and display the error if connection fails
    die("Connection failed: " . $conn->connect_error);
}
// If successful, the script continues silently!

// The $conn variable is now your usable database connection object.
?>