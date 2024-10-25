<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = ""; // Use your database password
$database = "smc_nstpms"; // Use your database name

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
