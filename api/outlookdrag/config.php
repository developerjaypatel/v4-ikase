<?php

// ==== DB CONFIG ====
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "outlookmaildb";

// ==== CONNECT DB ====
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB Connection failed: " . $conn->connect_error);
}
