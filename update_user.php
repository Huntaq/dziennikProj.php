<?php
// Połączenie z bazą danych
$servername = "localhost";
$username = "nazwa_uzytkownika";
$password = "haslo";
$dbname = "dziennik_lekcyjny";

$conn = new mysqli($servername, $username, $password, $dbname);

// Sprawdzenie połączenia
if ($conn->connect_error) {
    die("Połączenie nieudane: " . $conn->connect_error);
}

// Pobranie danych z żądania POST
$user_id = $_POST['user_id'];
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

// Aktualizacja danych w bazie danych
$sql = "UPDATE users SET username='$username', password='$password', role='$role' WHERE id='$user_id'";

if ($conn->query($sql) === TRUE) {
    echo 'success';
} else {
    echo 'error';
}

// Zamknięcie połączenia
$conn->close();
?>
