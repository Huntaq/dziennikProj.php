<?php
session_start();

require_once 'config.php';
require_once 'functions.php';

if (!isset($_SESSION['user']) || !isAdmin($_SESSION['user'])) {
    redirect('index.php');
}

if (isset($_POST['add_user'])) {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    createUser($conn, $username, $password, $role);
}

if (isset($_POST['edit_user'])) {
    // Wywołanie funkcji JavaScript, która wyświetla popup z formularzem edycji
    echo '<script>
            $(document).ready(function() {
                $("#editUserModal").modal("show");
                $("#edit_user_id").val("' . $_POST['user_id'] . '");
                $("#edit_username").val("' . $_POST['username'] . '");
                $("#edit_password").val("' . $_POST['password'] . '");
                $("#edit_role").val("' . $_POST['role'] . '");
            });
        </script>';
}

if (isset($_POST['update_user'])) {
    $userId = $_POST['user_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    updateUser($conn, $userId, $username, $password, $role);
}


if (isset($_POST['delete_user'])) {
    $userId = $_POST['user_id'];

    // Sprawdź, czy użytkownik nie próbuje usunąć samego siebie
    if ($userId != $_SESSION['user']['id']) {
        deleteUser($conn, $userId);
    } else {
        // Wyświetl komunikat o błędzie
        $_SESSION['message'] = "Nie możesz usunąć samego siebie.";
    }
}

$users = getAllUsers($conn);
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dziennik Lekcyjny - Zarządzanie użytkownikami</title>
    <!-- Dodanie linku do stylów Bootstrapa -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h1 class="mt-4">Zarządzanie użytkownikami</h1>

        <h2 class="mt-4">Dodaj nowego użytkownika</h2>
        <form method="POST">
            <div class="form-group">
                <label for="username">Nazwa użytkownika:</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="form-group">
                <label for="password">Hasło:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="role">Rola:</label>
                <select class="form-control" id="role" name="role">
                    <option value="administrator">Administrator</option>
                    <option value="nauczyciel">Nauczyciel</option>
                    <option value="uczen">Uczeń</option>
                </select>
            </div>

            <button type="submit" name="add_user" class="btn btn-primary">Dodaj użytkownika</button>
        </form>

        <h2 class="mt-4">Lista użytkowników</h2>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nazwa użytkownika</th>
                    <th>Rola</th>
                    <th>Akcje</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['role']; ?></td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="text" name="username" value="<?php echo $user['username']; ?>" hidden>
                                <input type="text" name="password" value="<?php echo $user['password']; ?>" hidden>
                                <input type="text" name="role" value="<?php echo $user['role']; ?>" hidden>
                                <button type="submit" name="edit_user" class="btn btn-primary btn-sm">Edytuj</button>
                                <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Usuń</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn btn-secondary mt-4">Powrót do panelu</a>
    </div>


       
    <!-- Dodanie skryptu Bootstrapa -->
    
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
   
</body>

</html>
