<?php
//Login-Include Dateien einbinden
require_once ( INC_PATH . "inc_user_login.php" );

// Logout-Nachricht anzeigen
// Template laden
$theTemplate = new Template ( "logout.tpl" );
$theTemplate->load ( "LOGOUT" );
echo $theTemplate;

// Dynamische Titel erweiterung setzen
$settings['title_ext'] = "Abgemeldet";
?>
