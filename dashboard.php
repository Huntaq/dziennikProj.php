<?php
session_start();

require_once 'functions.php';

if (!isset($_SESSION['user'])) {
    redirect('index.php');
}

$user = $_SESSION['user'];
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dziennik Lekcyjny - Panel</title>
    <!-- Dodanie linku do stylów Bootstrapa -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Witaj, <?php echo $user['username']; ?>!</h1>

        <?php if (isAdmin($user)) : ?>
            <!-- Strona dla administratora -->
            <h2>Panel administratora</h2>
            <ul class="list-group">
                <li class="list-group-item"><a href="users.php">Zarządzaj użytkownikami</a></li>
            </ul>
        <?php elseif (isTeacher($user)) : ?>
            <!-- Strona dla nauczyciela -->
            <h2>Panel nauczyciela</h2>
            <ul class="list-group">
                <li class="list-group-item"><a href="teacher.php">Dodaj oceny dla uczniów</a></li>
            </ul>
        <?php elseif (isStudent($user)) : ?>
            <!-- Strona dla ucznia -->
            <h2>Panel ucznia</h2>
            <ul class="list-group">
                <li class="list-group-item"><a href="student.php">Wyświetl oceny</a></li>
            </ul>
        <?php endif; ?>

        <a href="logout.php" class="btn btn-secondary mt-4">Wyloguj</a>
    </div>

    <!-- Dodanie skryptu Bootstrapa -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
