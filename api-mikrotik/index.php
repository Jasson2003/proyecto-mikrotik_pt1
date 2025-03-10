<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require('routeros_api.class.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>MikroTik API</title>
</head>
<body>
    <div class="container">
        <h1>Gestión de IPs en MikroTik</h1>
        <a href="logout.php">Cerrar sesión</a>

        <div class="tabs">
            <button class="tablink" onclick="openTab(event, 'addIp')">Agregar IP</button>
            <button class="tablink" onclick="openTab(event, 'viewIp')">Ver/Actualizar IP</button>
            <button class="tablink" onclick="openTab(event, 'bandwidthControl')">Control de Ancho de Banda</button>
            <button class="tablink" onclick="openTab(event, 'addUser')">Agregar Usuario</button>
            <button class="tablink" onclick="openTab(event, 'viewUsers')">Ver Usuarios</button>
        </div>

        <div id="addIp" class="tabcontent">
            <h2>Agregar IP</h2>
            <?php
            if (isset($_SESSION['message'])) {
                echo "<div class='alert " . $_SESSION['alert_class'] . "'>" . $_SESSION['message'] . "</div>";
                unset($_SESSION['message']);
                unset($_SESSION['alert_class']);
            }
            ?>
            <form action="add_ip.php" method="post">
                <label for="ip">Dirección IP (con máscara "/"):</label>
                <input type="text" id="ip" name="ip" required>

                <label for="interface">Interfaz:</label>
                <input type="text" id="interface" name="interface" required>

                <input type="submit" value="Agregar IP">
            </form>
        </div>

        <div id="viewIp" class="tabcontent" style="display:none;">
            <h2>Ver/Actualizar IP</h2>
            <form action="view_ip.php" method="post">
                <label for="current_ip">Dirección IP actual:</label>
                <input type="text" id="current_ip" name="current_ip" required>

                <input type="submit" value="Buscar">
            </form>
            <div id="ipDetails" style="display:none;">
                <h3>Detalles de la IP</h3>
                <form action="update_ip.php" method="post">
                    <label for="new_ip">Nueva Dirección IP:</label>
                    <input type="text" id="new_ip" name="new_ip" required>

                    <label for="new_interface">Nueva Interfaz:</label>
                    <input type="text" id="new_interface" name="new_interface" required>

                    <input type="hidden" name="old_ip" id="old_ip">
                    <input type="submit" value="Actualizar IP">
                </form>
            </div>
        </div>

        <div id="bandwidthControl" class="tabcontent" style="display:none;">
            <h2>Control de Ancho de Banda</h2>
            <form action="set_bandwidth.php" method="post">
            <label for="name">Nombre:</label>
            <input type="text" id="name" name="name" required>

            <label for="ip_bandwidth">Dirección IP (sin máscara "/"):</label>
            <input type="text" id="ip_bandwidth" name="ip_bandwidth" required>

            <label for="download_limit">Límite de Descarga (bits/s):</label>
            <input type="number" id="download_limit" name="download_limit" required min="1">

            <label for="upload_limit">Límite de Subida (bits/s):</label>
            <input type="number" id="upload_limit" name="upload_limit" required min="1">

            <input type="submit" value="Establecer Límite">
            </form>


            <h2>Lista de Ancho de Banda Registrada</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Dirección IP</th>
                        <th>Max / Limit</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $API = new RouterosAPI();

                    if ($API->connect('192.168.3.155', 'admin', 'admin')) {
                        $bandwidthRecords = $API->comm('/queue/simple/print');
                        foreach ($bandwidthRecords as $record) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($record['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($record['target']) . "</td>";
                            echo "<td>" . htmlspecialchars($record['max-limit']) . "</td>";
                            echo "<td>
                                    <button class='button-edit' onclick=\"editBandwidth('" . htmlspecialchars($record['name']) . "', '" . htmlspecialchars($record['target']) . "', '" . htmlspecialchars($record['max-limit']) . "');\">Editar</button>
                                    <button class='button-delete' onclick=\"deleteBandwidth('" . htmlspecialchars($record['name']) . "');\">Eliminar</button>
                                  </td>";
                            echo "</tr>";
                        }
                        $API->disconnect();
                    } else {
                        echo "<tr><td colspan='4'>Error al conectar a MikroTik.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="addUser" class="tabcontent" style="display:none;">
            <h2>Agregar Usuario</h2>
            <form action="add_user.php" method="post">
                <label for="username">Nombre de Usuario:</label>
                <input type="text" id="username" name="username" required>

                <label for="group">Grupo:</label>
                <select id="group" name="group" required>
                    <option value="read">Read</option>
                    <option value="full">Full</option>
                    <option value="write">Write</option>
                </select>
                
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>

                <label for="confirm_password">Confirmar Contraseña:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <input type="submit" value="Agregar Usuario">
            </form>
        </div>

        <div id="viewUsers" class="tabcontent" style="display:none;">
            <h2>Lista de Usuarios Registrados</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre de Usuario</th>
                        <th>Grupo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $API = new RouterosAPI();

                    if ($API->connect('192.168.3.155', 'admin', 'admin')) {
                        $users = $API->comm('/user/print');
                        foreach ($users as $user) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($user['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($user['group']) . "</td>";
                            echo "<td>
                                    <button class='button-delete' onclick=\"deleteUser('" . htmlspecialchars($user['name']) . "');\">Eliminar</button>
                                  </td>";
                            echo "</tr>";
                        }
                        $API->disconnect();
                    } else {
                        echo "<tr><td colspan='3'>Error al conectar a MikroTik.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <h2>Lista de IPs Registradas</h2>
        <table>
            <thead>
                <tr>
                    <th>Dirección IP</th>
                    <th>Interfaz</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $API = new RouterosAPI();

                if ($API->connect('192.168.3.155', 'admin', 'admin')) {
                    $addresses = $API->comm('/ip/address/print');
                    foreach ($addresses as $address) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($address['address']) . "</td>";
                        echo "<td>" . htmlspecialchars($address['interface']) . "</td>";
                        echo "<td>
                                <button class='button-edit' onclick=\"setIpData('" . $address['address'] . "', '" . $address['interface'] . "'); openTab(event, 'viewIp');\">Editar</button>
                                <button class='button-delete' onclick=\"deleteIp('" . htmlspecialchars($address['address']) . "');\">Eliminar</button>
                              </td>";
                        echo "</tr>";
                    }
                    $API->disconnect();
                } else {
                    echo "<tr><td colspan='3'>Error al conectar a MikroTik.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";  
            }
            tablinks = document.getElementsByClassName("tablink");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";  
            evt.currentTarget.className += " active";
        }

        function setIpData(ip, interface) {
            document.getElementById('old_ip').value = ip;
            document.getElementById('new_ip').value = ip;
            document.getElementById('new_interface').value = interface;
            document.getElementById('ipDetails').style.display = 'block';
        }

        function deleteIp(ip) {
            if (confirm('¿Estás seguro de que deseas eliminar la IP ' + ip + '?')) {
                window.location.href = 'delete_ip.php?ip=' + encodeURIComponent(ip);
            }
        }

        function deleteBandwidth(name) {
            if (confirm('¿Estás seguro de que deseas eliminar el ancho de banda para ' + name + '?')) {
                window.location.href = 'delete_bandwidth.php?name=' + encodeURIComponent(name);
            }
        }

        function deleteUser(username) {
            if (confirm('¿Estás seguro de que deseas eliminar el usuario ' + username + '?')) {
                window.location.href = 'delete_user.php?username=' + encodeURIComponent(username);
            }
        }

        function editBandwidth(name, ip_bandwidth, max_limit) {
            document.getElementById('name').value = name;
            document.getElementById('ip_bandwidth').value = ip_bandwidth;
            
            // Suponiendo que el 'max-limit' es un valor en el formato "descarga/subida"
            const limits = max_limit.split('/');
            document.getElementById('download_limit').value = limits[0]; // Límite de Descarga
            document.getElementById('upload_limit').value = limits[1]; // Límite de Subida
        }


    </script>
</body>
</html>
