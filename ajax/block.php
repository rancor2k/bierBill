<?php
// Starte Session
session_start ();

// Magic Quotes deaktivieren
set_magic_quotes_runtime ( FALSE );

// include-Pfad setzen
set_include_path ( '.' );
define ( 'INC_PATH', "./../includes/", TRUE );

// Datenbank- & Config-Datei einbinden
require ( INC_PATH . "inc_login.php");

// Datenbankverbindung konnte hergestellt werden
if ( $db !== FALSE ) {
    // Abgelaufene Blockierungen löschen
    $sql->deleteData ( "blocked", "`blocked_time` <= ".(time()-90)."");
    // Nur Falls auch alle Daten übertragen wurden
    if (
        isset ( $_POST['user_id'] )
        && isset ( $_POST['show_id'] )
        && isset ( $_POST['seat'] )
    ) {
        settype ( $_POST['user_id'], "integer" ); // vor MySQL Injections sichern
        settype ( $_POST['show_id'], "integer" ); // vor MySQL Injections sichern
        settype ( $_POST['seat'], "integer" ); // vor MySQL Injections sichern
        
        // Blockierung in DB eintragen
        $sql->setData ( "blocked", "user_id,show_id,blocked_seats,blocked_time", array ( $_POST['user_id'], $_POST['show_id'], $_POST['seat'], time() ) );
    }
}