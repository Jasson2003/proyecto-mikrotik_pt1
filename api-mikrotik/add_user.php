<?php
session_start();
require('routeros_api.class.php'); // Asegúrate de incluir tu clase API

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $group = $_POST['group'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Las contraseñas no coinciden.";
        $_SESSION['alert_class'] = "error"; // Define estilos en CSS
    } else {
        // Conexión a MikroTik y agregar usuario
        $API = new RouterosAPI();
        if ($API->connect('192.168.3.155', 'admin', 'admin')) {
            $API->comm('/user/add', [
                'name' => $username,
                'group' => $group,
                'password' => $password,
            ]);
            $API->disconnect();

            $_SESSION['message'] = "Usuario agregado exitosamente.";
            $_SESSION['alert_class'] = "success"; // Define estilos en CSS
        } else {
            $_SESSION['message'] = "Error al conectar con MikroTik.";
            $_SESSION['alert_class'] = "error";
        }
    }

    header("Location: index.php");
    exit;
}
