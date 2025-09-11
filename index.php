
<?php

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /admin/login.php');
    exit;
}else{
    header('Location: /admin/dashboard.php');
    exit;
}
?>