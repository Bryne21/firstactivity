<?php
$conn = new mysqli("localhost","root","root","test_db");

$sql = "INSERT INTO tblusers ( fldName, fldEmail, fldPassword, fldUsername)
VALUES ('sir', 'hed-sir@smu.edu.ph', 'student', 'student')";

if ($conn->query($sql) === TRUE) {
    echo 'New Record created!';
} else {
    echo 'Error'. $conn->error;
}

$conn->close();

?>