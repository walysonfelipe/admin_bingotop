<?php

$servername = "localhost";
$username = "bingotop_admin";
$password = "3E@lm2xop1";
$dbname = "bingotop_db";

// $servername = "localhost";
// $username = "root";
// $password = "";
// $dbname = "u633395522_showpix";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
