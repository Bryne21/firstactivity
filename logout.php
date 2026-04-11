<?php
session_start();


$_SESSION =[];


session_destroy();


header("Location: loginform.php");
exit;
?>