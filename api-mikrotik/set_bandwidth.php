<?php
session_start();
require('routeros_api.class.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $ip_bandwidth = $_POST['ip_bandwidth'];
    $download_limit = $_POST['download_limit'];
    $upload_limit = $_POST['upload_limit'];

    $API = new RouterosAPI();

    if ($API->connect('192.168.3.155', 'admin', 'admin')) {
        // Obtener la lista de IPs registradas
        $addresses = $API->comm('/ip/address/print');
        
        // Verificar si la IP ya está registrada
        $isRegistered = false;
        foreach ($addresses as $address) {
            // Comparar la dirección IP sin la máscara
            if (strpos($address['address'], '/') !== false) {
                $registeredIp = explode('/', $address['address'])[0];
            } else {
                $registeredIp = $address['address'];
            }

            if ($registeredIp === $ip_bandwidth) {
                $isRegistered = true;
                break;
            }
        }

        // Si la IP no está registrada, mostrar mensaje de error
        if (!$isRegistered) {
            $_SESSION['message'] = "La IP no está registrada. No se puede establecer el límite.";
            $_SESSION['alert_class'] = 'alert-danger';
        } else {
            // Si la IP está registrada, se establece el límite de ancho de banda
            $API->comm('/queue/simple/add', [
                'name' => $name,
                'target' => $ip_bandwidth,
                'max-limit' => $download_limit . '/' . $upload_limit
            ]);
            $_SESSION['message'] = "Ancho de banda actualizado exitosamente.";
            $_SESSION['alert_class'] = "alert-success";
        }

        $API->disconnect();
    } else {
        $_SESSION['message'] = "Error al conectar a MikroTik.";
        $_SESSION['alert_class'] = "alert-danger";
    }
}

header("location: index.php");
?>
