<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "tdjoneschat";
//Database connection
//$host = "web-app.cypdsirurk3x.us-east-1.rds.amazonaws.com";
//$username = "admin";
//$password = "adminhealtshiftpass";
//$database = "Newhsdb";

$con = mysqli_connect($host, $username, $password, $database);

if (!$con) {
    die("Connection Failed" . mysqli_connect_error());
} else {
}
