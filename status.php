<?php
// status.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? '';
    
    if ($user_id) {
        try {
            $db = new PDO('sqlite:usuarios.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Actualizar la fecha para indicar que el usuario está activo
            $stmt = $db->prepare("UPDATE usuarios SET fecha = ? WHERE id = ?");
            $stmt->execute([date('Y-m-d H:i:s'), $user_id]);
            
            echo "OK";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>