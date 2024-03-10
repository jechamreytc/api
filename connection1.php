<?php
$servername1 = "localhost";
$dbusername1 = "root";
$dbpassword1 = "";
$dbname1 = "dbsocial_media";

try {
    $conn = new PDO("mysql:host=$servername1;dbname=$dbname1", $dbusername1, $dbpassword1);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
