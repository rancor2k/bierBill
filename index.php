<?php
// Starte Session
session_start ();

// Magic Quotes deaktivieren
set_magic_quotes_runtime ( FALSE );

// include-Pfad setzen
set_include_path ( '.' );
define ( 'ROOT_PATH', "./", TRUE );
define ( 'INC_PATH', "./includes/", TRUE );

// Datenbank- & Config-Datei einbinden
require ( INC_PATH . "inc_db_login.php");

// Datenbankverbindung konnte hergestellt werden
if ( $db !== FALSE ) {
    //Include Dateien einbinden
    require ( INC_PATH . "inc_functions.php" );
    
    //Klassen einbinden (DB Klasse wurde bereits eingebunden)
    require ( INC_PATH . "class_fileaccess.php" );
    require ( INC_PATH . "class_template.php" );
    require ( INC_PATH . "class_screen.php" );

    // Allgemeine Funktionen aufrufen
    getGoTo ( $_GET['go'] );

    // Seiten-Template generieren
    $theTemplate = new Template ( "main.tpl" );
    
    $theTemplate->load ( "DOCTYPE" ); // Doctype laden
    $template['doc'] = (string) $theTemplate; // Typecasting, da sonst als Referenz zwischengespeichert

    // Menüs laden
    for ( $i=1; $theTemplate->load ( "MENU".$i ); $i++ ) {
        $template['menu'][$i] = (string) $theTemplate;  // Erlaubt theortisch unendlich viele Menüs, wenn diese in der Template-Datei eingetragen werden, genutzt wird allerdings nur eines
    }

    // Body erzeugen
    $theTemplate->load ( "BODY" );
    $theTemplate->tag ( "content", getContent ( $settings['goto'] ) );
    foreach ( $template['menu'] as $number => $aMenu ) {
        $theTemplate->tag ( "menu".$number, $aMenu );
    }
    $template['body'] = (string) $theTemplate;
    
    // Haupt-Template laden
    $output = getMainTemplate ();
    $output = str_replace ( "{..body..}", $template['body'], $output );
    $output = str_replace ( "{..doctype..}", $template['doc'], $output );

    // Seite ausgeben
    echo $output;

    // DB-Objekt zerstören => Verbindung beenden
    unset ( $db );
}

// Keine Datenbankverbindung => Hinweis darauf
else {
    echo'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>High Noon Kino Tübingen</title>
    </head>
    <body>
        <p>
            Leider gibt es zurzeit ein Problem mit unserem Server.<br>
            Bitte versuchen Sie es später noch einmal.
        </p>
        <p>
            Vielen Dank<br>
            Ihr Team vom High Noon Kino Tübingen
        </p>
    </body>
</html>';
}
?>
