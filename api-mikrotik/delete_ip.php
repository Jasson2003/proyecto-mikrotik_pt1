<?php
session_start();
require('routeros_api.class.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

if (isset($_GET['ip'])) {
    $ip = $_GET['ip'];

    $API = new RouterosAPI();
    if ($API->connect('192.168.3.155', 'admin', 'admin')) {
        // Encuentra el ID del address
        $addressList = $API->comm('/ip/address/print');
        foreach ($addressList as $address) {
            if ($address['address'] === $ip) {
                $id = $address['.id'];
                break;
            }
        }

        // Si se encuentra el ID, eliminarlo
        if (isset($id)) {
            $API->comm('/ip/address/remove', array('.id' => $id));
            $_SESSION['message'] = "La IP $ip se ha eliminado correctamente.";
            $_SESSION['alert_class'] = "alert-success";
        } else {
            $_SESSION['message'] = "La IP $ip no se encontró.";
            $_SESSION['alert_class'] = "alert-danger";
        }
        $API->disconnect();
    } else {
        $_SESSION['message'] = "Error al conectar a MikroTik.";
        $_SESSION['alert_class'] = "alert-danger";
    }
}

header("location: index.php");
exit;
?>
