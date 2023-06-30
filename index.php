<?php
session_start();

require_once 'config.php';
require_once 'functions.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $user = authenticate($conn, $username, $password);

    if ($user) {
        $_SESSION['user'] = $user;
        redirect('dashboard.php');
    } else {
        $error = 'Nieprawidłowa nazwa użytkownika lub hasło.';
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Dziennik Lekcyjny - Logowanie</title>
    <!-- Dodanie linku do stylów Bootstrapa -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-5">Logowanie</h1>

        <?php if (isset($error)) : ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="username">Nazwa użytkownika:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Hasło:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary">Zaloguj</button>
        </form>
    </div>

    <!-- Dodanie skryptu Bootstrapa -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>
