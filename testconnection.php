<?php
$servername = "localhost";
$username = "root"; 
$password = "root";
$database = "test_db";

//Create connection
$conn = new mysqli($servername, $username, $password, $database);

//Check connection
if ($conn->connect_error) {
    die("Connection Failed:". $conn->connect_error);
}
//echo "Connected Successfully";
?>