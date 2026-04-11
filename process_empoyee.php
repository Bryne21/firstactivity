<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Default XAMPP username
$password = "";     // Default XAMPP password
$dbname = "your_db_name"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize data
    $firstname   = $conn->real_escape_string($_POST['firstname']);
    $middlename  = $conn->real_escape_string($_POST['middlename']);
    $lastname    = $conn->real_escape_string($_POST['lastname']);
    $position    = $conn->real_escape_string($_POST['position']);
    $address     = $conn->real_escape_string($_POST['address']);
    $dob         = $conn->real_escape_string($_POST['dateofbirth']);
    $rateperday  = $conn->real_escape_string($_POST['rateperday']);
    $employeeId  = $conn->real_escape_string($_POST['employeeId']);

    // SQL to insert data (Ensure table name and columns match your DB)
    $sql = "INSERT INTO employees (firstname, middlename, lastname, position, address, dateofbirth, rateperday, employeeId) 
            VALUES ('$firstname', '$middlename', '$lastname', '$position', '$address', '$dob', '$rateperday', '$employeeId')";

    if ($conn->query($sql) === TRUE) {
        // Success: Redirect to the view page
        header("Location: view_employee.php?status=success");
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>