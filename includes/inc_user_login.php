<?php

// Login abfangen
if ( $_POST['login'] == 1 ) {
    login ( $_POST['user'], $_POST['pass'] );
}

// Logout abfangen
if ( $_GET['go'] == "logout" && $_POST['login'] != 1 ) {
    logout();
}


// Funktion die den Login Vorgang regelt
function login ( $login_name, $password ) {
    // DB-Klasse verfügbar machen
    global $sql;

    // Benutzername und Passwort anpassen
    secureSQL ( $login_name );
    $password = md5 ( $password ); // Muss nicht gesichert werden, da ein MD5 Hash niemals schädlichen Code enthält
    
    // Nachschlagen ob es diese Kombination gibt
    $login_data = $sql->getData ( "users", "id", "WHERE `name` = '".$login_name."' AND `password` = '".$password."'", 1 );

    // Wenn ja => Session setzen
    if ( $sql->wasGetSuccessful ( $login_data  ) ) {
        $_SESSION["login"] = "ok";
        return TRUE;
    } else {
        logout();
        return FALSE;
    }
}


// Loggt einen User aus, bzw. löscht alles was man so mit Sessions machen kann
function logout () {
    session_unset (); // Session-Variablen löschen
    session_destroy (); // Session zerstören
    $_SESSION = array(); // Superglobale mit leerem Array überschreiben
}
?>
