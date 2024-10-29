<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require('routeros_api.class.php');

$API = new RouterosAPI();
if ($API->connect('192.168.3.155', 'admin', 'admin')) {
    if (isset($_GET['username'])) {
        $username = $_GET['username'];
        // Ejecuta el comando para eliminar el usuario
        $API->comm('/user/remove', ['.id' => $username]);
        $_SESSION['message'] = "Usuario $username eliminado con éxito.";
        $_SESSION['alert_class'] = "alert-success";
    } else {
        $_SESSION['message'] = "No se proporcionó un nombre de usuario.";
        $_SESSION['alert_class'] = "alert-danger";
    }
    $API->disconnect();
} else {
    $_SESSION['message'] = "Error al conectar a MikroTik.";
    $_SESSION['alert_class'] = "alert-danger";
}

header("Location: index.php");
exit;
?>
