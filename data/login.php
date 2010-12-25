<?php
//Login-Include Dateien einbinden
require_once ( INC_PATH . "inc_user_login.php" );

// Wenn der Benutzer eingeloggt ist...
if ( $_SESSION["login"] == "ok" ) {
    // ... Weiterleitung zur Admin-Seite
    include ( ROOT_PATH."data/admin.php");
    
// Sonst Login-Formular ausgeben
} else {
    // Template laden
    $theTemplate = new Template ( "admin.tpl" );
    
    if ( isset ( $_POST['login'] ) ) {
        // Fehler-Template laden
        $theTemplate->load ( "LOGIN_ERROR" );
        $error = (string) $theTemplate;
    } else {
        $error = "";
    }
    // Formular-Template laden und ausgeben
    $theTemplate->load ( "LOGIN" );
    $theTemplate->tag ( "error", $error );
    echo $theTemplate;
}

// Dynamische Titel erweiterung setzen
$settings['title_ext'] = "Verwaltung";
?>
