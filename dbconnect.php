<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecalpal";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Shanghai');
session_start();
?>