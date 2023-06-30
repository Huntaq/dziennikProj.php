<?php
require_once 'config.php';
require_once 'functions.php';

$username = 'admin';
$password = 'adminpassword';
$role = 'administrator';

createUser($conn, $username, $password, $role);
?>
