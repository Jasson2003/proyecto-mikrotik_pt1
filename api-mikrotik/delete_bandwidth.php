<?php
session_start();
require('routeros_api.class.php');

if (isset($_GET['name'])) {
    $name = $_GET['name'];

    $API = new RouterosAPI();

    if ($API->connect('192.168.3.155', 'admin', 'admin')) {
        // Eliminar el ancho de banda
        $API->comm('/queue/simple/remove', ['.id' => $name]);
        $API->disconnect();

        $_SESSION['message'] = "Ancho de banda eliminado exitosamente.";
        $_SESSION['alert_class'] = "alert-success";
    } else {
        $_SESSION['message'] = "Error al conectar a MikroTik.";
        $_SESSION['alert_class'] = "alert-danger";
    }
}

header("location: index.php");
?>
