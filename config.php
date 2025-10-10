<?php
// Connect database แบบ mysqli
$host = "localhost";
$username = "root";
$password = "";
$database = "online_shop";

// สร้าง mysqli connection
$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$dns = "mysql:host=$host;dbname=$database";

// connect database แบบ PDO
try {
    $pdo = new PDO($dns, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "PDO: Connected successfully";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>