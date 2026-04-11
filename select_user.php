<?php
$conn = new mysqli("localhost","root","root","test_db");

$result = $conn->query("SELECT * FROM tblusers");

while ($row = $result->fetch_assoc()) {
    echo "Name: ".$row["fldName"]."<br>Email:".$row["fldEmail"] . "<br> <br>";
    }
    $conn->close();
    ?>