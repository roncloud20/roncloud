<?php
// Database connection configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "roncloud_db";

// Create a connection to the database
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
