<?php
session_start();

// Función para obtener la IP real del cliente
function getUserIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$ip = getUserIP();
$archivo = 'solicitudes.json';

// Verificar si hay un ID de usuario específico
if (isset($_GET["id"])) {
    $idUsuario = $_GET["id"];
    
    try {
        $db = new PDO('sqlite:usuarios.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $db->prepare("SELECT estado FROM usuarios WHERE id = ?");
        $stmt->execute([$idUsuario]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            $estado = $usuario['estado'];
        } else {
            $estado = 'error';
        }
    } catch (Exception $e) {
        $estado = 'error';
    }
} else {
    // Si no hay ID, usar el sistema antiguo de IP
    if (isset($_SESSION['ip'])) {
        $solicitudes = file_exists($archivo) ? json_decode(file_get_contents($archivo), true) : [];
        $estado = $solicitudes[$_SESSION['ip']]['estado'] ?? 'esperando';
    } else {
        $estado = 'esperando';
    }
}

header('Content-Type: application/json');
echo json_encode(['estado' => $estado]);
?>