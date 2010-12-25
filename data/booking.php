<?php
// Cookie löschen wenn paramter delete übergeben
if ( isset ( $_GET['delete'] ) ) {
    // Cookie Ablaufdatum in Vergangenheit setzen
    setcookie ( "kino_user", "", time()-60*60*24*30 );
}

// booking nur durchführen, wenn id übergeben
if ( isset ( $_GET['id'] ) ) {
    settype ( $_GET['id'], "integer" ); // ID vor Injections sichern

    if (
        isset ( $_POST['booknow'] ) // Nur Falls der "jetzt reservieren button gedrückt wurde
        && isset ( $_POST['seats'] ) && is_array ( $_POST['seats'] ) // aber auch nur wenn auch plätze ausgewählt wurden
        && isset ( $_POST['user_id'] ) // und der benutzer muss natürlich auch mit übergeben werden
        && isset ( $_POST['user_hash'] ) // inkl. hash
    ) {
        settype ( $_POST['user_id'], "integer" ); // ID vor Injections sichern
        secureSQL ( &$_POST['user_hash'] );  // Hash vor Injections sichern

        // Alle markierten Sitze durchgehen...
        foreach ( $_POST['seats'] as $key => $value ) {
            settype ( $_POST['seats'][$key], "integer" ); // ... und vor Injections sichern
        }

        // Benutzerdaten aus der Datenbank laden
        $user_data = $sql->getData ( "user", "*", "WHERE `user_id` = '".$_POST['user_id']."' AND `user_hash` = '".$_POST['user_hash']."'", 1 ); // Daten aus DB lesen

        // Ticket Daten für die gewählte Show un den eingeloggten User laden
        $ticket_data = getTicketData ( $_GET['id'], $user_data['user_id'] );

        // Template laden
        $theTemplate = new Template ( "booking.tpl" );

        // Wenn der Benutzer existiert (erneute Abfrage soll Manipulation verhindern
        // Und wenn es keine überschneidungen mit bereits reservierten, gekauften oder nicht für diesen Benutzer blockierten Plätzen gibt
        if (
            $sql->wasGetSuccessful ( $user_data )
            && count ( array_intersect ( $_POST['seats'], $ticket_data[0] ) ) == 0
            && count ( array_intersect ( $_POST['seats'], $ticket_data[1] ) ) == 0
            && count ( array_intersect ( $_POST['seats'], $ticket_data[2] ) ) == 0
        ) {
            // Ticket-Daten in DB speichern
            $sql->setData ( "tickets", "user_id,show_id,ticket_seats,ticket_sold", array ( $user_data['user_id'], $_GET['id'], implode ( ",", $_POST['seats'] ), 0 ) );

            // Bestätigungs Template laden
            $theTemplate->load ( "BOOKING_END" );
            $theTemplate->clearTags ();
            $theTemplate->tag ( "prename", $user_data['user_prename'] ); // Vorname einfügen
            $theTemplate->tag ( "lastname", $user_data['user_lastname'] ); // Nachname einfügen
            $theTemplate->tag ( "email", $user_data['user_email'] ); // E-Mail einfügen
            $theTemplate->tag ( "show_info", getShowInfoHTML ( $_GET['id'] ) );  // Vorführungsdaten laden und einfügen
            
            $theTemplate->tag ( "error", "" );  // Fehler wird nicht angezeigt
            $theTemplate->tag ( "seats", implode ( ", ", $_POST['seats'] ) );  // Nummern der Sitze nochmal anzeigen
            $theTemplate->tag ( "price",  (string) (count ( $_POST['seats'] )*5).",00€" ); // vorläufigen Preis berechnen [derzeit hardcoded, ansonsten müsste man eben noch ein preis-system anbasteln]
            $theTemplate->tag ( "num_seats", count ( $_POST['seats'] ) ); // Anzahl der Plätze ausgeben

        // Es gab überschneidungen mit reservierten, gekauften oder blockierten Plätzen
        // Benutzer-Manipulation wird hier nicht abgefangen, dies passiert dann sowieso wieder wenn die Platzauswahl geladen wird
        } else {
            // Fehler Template laden
            $theTemplate->load ( "BOOKING_ERROR" );
            $theTemplate->clearTags ();
            $theTemplate->tag ( "url", "?go=booking&id=".$_GET['id'] ); // Link setzen
        }
        
        // Das Template ausgeben
        echo $theTemplate;
    } // END IF Buchungs-Abschluss

    // Keine Buchung abgeschickt
    else {

        // keine abfrage falls cookie vorhanden und daten in DB
        $user_exists = FALSE;
        if ( isset ( $_COOKIE['kino_user'] ) ) {
            $user_hash = secureSQL ( substr ( $_COOKIE['kino_user'], 0, 32 ) ); // MD5-Hash nehmen und sichern [muss hier gesichert werden, da die Daten vom User kommen]
            $user_id = substr ( $_COOKIE['kino_user'], 32 ); // User-ID nehmen
            settype ( $user_id, "integer" ); // User ID sichern
            $user_data = $sql->getData ( "user", "*", "WHERE `user_id` = '".$user_id."' AND `user_hash` = '".$user_hash."'", 1 ); // Daten aus DB lesen

            // User Gefunden
            if ( $sql->wasGetSuccessful ( $user_data ) ) {
                $user_exists = TRUE;
                // User Daten in POST zur weiteren verarbeitung
                $_POST['email'] = $user_data['user_email'];
                $_POST['prename'] = $user_data['user_prename'];
                $_POST['lastname'] = $user_data['user_lastname'];
            }
        }

        // Checken ob Anmelde-Daten vorhanden sind
        if (
            $user_exists
            || ( checkFormData ( $_POST['email'], "email", TRUE ) // Eingaben Serverseitig validieren
                && checkFormData ( $_POST['prename'], "text", TRUE, "[a-zA-Z ]{2,50}" ) // Eingaben Serverseitig validieren
                && checkFormData ( $_POST['lastname'], "text", TRUE, "[a-zA-Z ]{2,50}" ) // Eingaben Serverseitig validieren
            )
        ) {
            // Benutzer Cookie anlegen und in DB speichern, falls nicht schon alles perfekt ist
            if ( !$user_exists ) {
                // Nachschauen ob Benutzerdaten schon in DB sind
                $user_data = $sql->getData ( "user", "user_hash,user_id", "WHERE `user_email` = '".secureSQL($_POST['email'])."' AND `user_prename` = '".secureSQL($_POST['prename'])."' AND `user_lastname` = '".secureSQL($_POST['lastname'])."'", 1 );

                // Falls er nicht existiert => anlegen
                if ( $user_data == 0 ) {
                    // praktisch eindeutigen Hash erzeugen
                    $user_hash = md5( $_POST['email'].$_POST['prename'].$_POST['lastname'] );
                    // in DB ablegen
                    $sql->setData ( "user", "user_hash,user_email,user_prename,user_lastname", $user_hash . "," . secureSQL ( $_POST['email'] ) . "," . secureSQL ( $_POST['prename'] ) . "," . secureSQL ( $_POST['lastname'] ) );
                    $user_id = $sql->getInsertId();
                // Wenn er schon exisitiert => Daten in richtigen Variablen ablegen, damit der Cookie korrekt gesetzt werden kann
                } else {
                    $user_hash = $user_data['user_hash'];
                    $user_id = $user_data['user_id'];
                }
                // In jedem Fall Cookie setzen
                setcookie ( "kino_user", $user_hash.$user_id, time()+60*60*24*30 ); // MD5-Hash + eindeutige DB ID, Ablauf in 30 Tagen
            }

            // Show, Film und Kino Daten laden
            $show_data = mysql_fetch_assoc ( $sql->query ( "SELECT * FROM {..pref..}running_weeks W, {..pref..}running_table T WHERE T.`show_id` = '".$_GET['id']."' AND T.`running_weeks_id` = W.`running_weeks_id`") );
            $screen_data = $sql->getData ( "screens", "*", "WHERE `screen_id` = '".$show_data['screen_id']."'", 1 );

            // Template laden
            $theTemplate = new Template ( "booking.tpl" );

            // Booking Info erstellen
            $theTemplate->load ( "BOOKING_INFO" );
            $theTemplate->clearTags ();
            $booking_info = (string) $theTemplate;

            // Persönliche Begrüßung erstellen
            $theTemplate->load ( "PERSONAL" );
            $theTemplate->clearTags ();
            $theTemplate->tag ( "email", $_POST['email'] );
            $theTemplate->tag ( "prename", $_POST['prename'] );
            $theTemplate->tag ( "lastname", $_POST['lastname'] );
            $theTemplate->tag ( "delete_url", "?go=booking&id=".$_GET['id']."&delete" );
            $personal = (string) $theTemplate;

            // Saal laden
            $screen = new ScreenRoom ( $screen_data['screen_seats'] );
            $screen->setStates ( $_GET['id'], $user_id );

            // Legende laden
            $theTemplate->load ( "LEGEND" );
            $theTemplate->clearTags ();
            $legend = (string) $theTemplate;

            // Template laden
            $theTemplate->load ( "BOOKING" );
            $theTemplate->clearTags ();
            $theTemplate->tag ( "user_hash", $user_hash );
            $theTemplate->tag ( "user_id", $user_id );
            $theTemplate->tag ( "show_id", $_GET['id'] );
            $theTemplate->tag ( "personal", $personal );
            $theTemplate->tag ( "show_info", getShowInfoHTML ( $_GET['id'] ) );
            $theTemplate->tag ( "booking_info", $booking_info );
            $theTemplate->tag ( "screen", (string) $screen );
            $theTemplate->tag ( "legend", $legend );

            // Buchungs-Template ausgeben
            echo $theTemplate;

        }  // END IF Buchungs-Formular

        // Formular noch nicht abgeschickt oder Formulardaten entsprechen nicht den Vorgaben
        else {
            // Template laden
            $theTemplate = new Template ( "booking.tpl" );
            $theTemplate->load ( "MAILFORM" );
            $theTemplate->tag ( "post_email", "" );
            $theTemplate->tag ( "post_prename", "" );
            $theTemplate->tag ( "post_lasname", "" );

            // Fals das Formular schon abgeschickt wurde => Fehler auswertung
            if ( $_POST['sended'] == 1 ) {
                $theTemplate->tag ( "post_email", killhtml ( $_POST['email'] ) ); // eingebenen Wert vorausfüllen
                $theTemplate->tag ( "post_prename", killhtml ( $_POST['prename'] ) ); // eingebenen Wert vorausfüllen
                $theTemplate->tag ( "post_lasname", killhtml ( $_POST['lastname'] ) ); // eingebenen Wert vorausfüllen

                // Fehler behandlung
                if ( !checkFormData ( $_POST['email'], "email", TRUE ) ) {
                    echo '<div class="error" style="visibility: visible; position: absolute; top: 310px; left: 430px;"><span>Bitte tragen Sie eine gültige E-Mail-Adresse ein</span></div>';
                }
                if ( !checkFormData ( $_POST['prename'], "text", TRUE, "[a-zA-Z ]{2,50}" ) ) {
                    echo '<div class="error" style="visibility: visible; position: absolute; top: 364px; left: 430px;"><span>Bitte tragen Sie einen gültigen Wert ein</span></div>';
                }
                if ( !checkFormData ( $_POST['lastname'], "text", TRUE, "[a-zA-Z ]{2,50}" ) ) {
                    echo '<div class="error" style="visibility: visible; position: absolute; top: 419px; left: 430px;"><span>Bitte tragen Sie einen gültigen Wert ein</span></div>';
                }
            }

            // Formular-Template ausgeben
            echo $theTemplate;
        } // END ELSE User-Formular
        
    }  // END ELSE keine Buchung abgeschickt

// Sonst einfach die Reservierungsübrsicht laden
} else {
    include ( ROOT_PATH."data/reservierung.php");
}

// Dynamische Titel erweiterung setzen
$settings['title_ext'] = "Online-Reservierung";
?>