<?php
    $host = 'localhost';
    $dbname = 'carlyn_cake_shop';
    $username = 'root';
    $password = '';

    $conn = new mysqli($host, $username, $password, $dbname);

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Database connection failed: " . $e->getMessage());
    }
?>