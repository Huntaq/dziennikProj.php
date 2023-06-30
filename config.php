<?php
$host = 'localhost';
$db = 'dziennik_lekcyjny';
$user = 'root';
$password = '';

$conn = new PDO("mysql:host=$host;dbname=$db", $user, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
