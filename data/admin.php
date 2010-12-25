<?php
//Login-Include Dateien einbinden
require_once ( INC_PATH . "inc_login.php" );

// Wenn der Benutzer eingeloggt ist
if ( $_SESSION["login"] == "ok" ) {
    
    // Buchungs-Übersicht anzeigen, wenn die Show-ID existiert
    if ( isset ( $_GET['id'] ) ) {
        settype ( $_GET['id'], "integer" ); // ID vor Injections sichern

        // Alle Buchungen löschen
        if ( isset ( $_POST['reset'] ) && $_POST['reset'] == 1 ) {
            // Buchungen löschen
            $sql->deleteData ( "tickets", "`show_id` = '".$_GET['id']."'" );
            $message_title = "Vorstellung zurückgesetzt";
            $message_text = "Die Vorstellung wurde wie gewünscht zurückgesetzt. Es wurden alle Reservierungen und Käufe gelöscht. Viel Spaß mit den verägerten Kunden.";
        }
        
        // Eine einzelne Buchung löschen
        if ( isset ( $_GET['del'] ) ) {
            settype ( $_GET['del'], "integer" ); // ID vor Injections sichern
            // Buchung löschen
            $sql->deleteData ( "tickets", "`ticket_id` = '".$_GET['del']."'", "LIMIT 1" );
            $message_title = "Buchung gelöscht";
            $message_text = "Die Buchung #".$_GET['del']." wurde gelöscht!";
            unset ( $_GET['del'] );
        }

        // Template laden
        $theTemplate = new Template ( "admin.tpl" );
        
        // Buchungs Daten laden
        $booking_data = $sql->getData ( "tickets", "*", "WHERE `show_id` = '".$_GET['id']."' ORDER BY `ticket_id`" );
        $booking_data = $sql->wasGetSuccessful ( $booking_data ) ? $booking_data : array();
        
        // Variablen leer initialisieren
        $reservations = "";
        $sales = "";
        
        // Jede Buchung durchgehen
        foreach ( $booking_data as $aBooking ) {
            // Userdaten laden
            $user_data = $sql->getData ( "user", "*", "WHERE `user_id` = '".$aBooking['user_id']."'", 1 );

            // Sitze auslesen
            $theSeats = explode ( ",", $aBooking['ticket_seats'] );
            $numSeats = count ( $theSeats );

            // Template für eine Buchung laden
            $theTemplate->load ( "BOOKING" );
            $theTemplate->clearTags ();
            $theTemplate->tag ( "lastname", $user_data['user_lastname'] ); // Nachname des Buchers
            $theTemplate->tag ( "prename", $user_data['user_prename'] ); // Vorname des Buchers
            $theTemplate->tag ( "email", $user_data['user_email'] ); // E-Mail des Buchers
            $theTemplate->tag ( "seats", implode ( ", ", $theSeats ) ); // gebuchte Plätze
            $theTemplate->tag ( "num_seats", $numSeats ); // Anzahl der gebuchten Plätze
            $theTemplate->tag ( "price", (string) ($numSeats*5).",00€" ); // vorläufiger Preis
            $theTemplate->tag ( "delete_url", "?go=admin&id=".$_GET['id']."&del=". $aBooking['ticket_id'] ); // URL um Buchung zu löschen

            // Buchung ist ein Verkauf
            if ( $aBooking['ticket_sold'] == 1 ) {
                $sales .= (string) $theTemplate;
            // Buchung ist eine Reservierung
            } else {
                $reservations .= (string) $theTemplate;
            }
        }

        // Reservierungen-Template laden
        if ( $reservations != "" ) {
            $theTemplate->load ( "SHOW_RESERVATIONS" );
            $theTemplate->clearTags ();
            $theTemplate->tag ( "bookings", $reservations );
            $reservations = (string) $theTemplate;
        }
        
        // Verkaufs-Template laden
        if ( $sales != "" ) {
            $theTemplate->load ( "SHOW_SALES" );
            $theTemplate->clearTags ();
            $theTemplate->tag ( "bookings", $sales );
            $sales = (string) $theTemplate;
        }

        // Woche der Vorstellung ermitteln
        $show_week = mysql_result ( $sql->query ( "SELECT W.`running_week` FROM `{..pref..}running_weeks` W, `{..pref..}running_table` T WHERE T.`show_id` = '".$_GET['id']."' AND T.`running_weeks_id` = W.`running_weeks_id` LIMIT 0,1" ), 0, "running_week" );
        
        // Nachricht erstellen
        if ( $message_title != "" && $message_text != "" ) {
            $theTemplate->load ( "MESSAGE" );
            $theTemplate->clearTags ();
            $theTemplate->tag ( "title", $message_title );
            $theTemplate->tag ( "text", $message_text );
            $message = (string) $theTemplate;
        } else {
            $message = "";
        }
        
        // Reset-Button
        $theTemplate->load ( "RESET" );
        $theTemplate->clearTags ();
        $theTemplate->tag ( "show_id", $_GET['id'] );
        $reset = (string) $theTemplate;

        // Show-Template laden
        $theTemplate->load ( "SHOW" );
        $theTemplate->clearTags ();
        $theTemplate->tag ( "url", "?go=admin&week=".$show_week  ); // URL zur Wochenübersicht
        $theTemplate->tag ( "show_info", getShowInfoHTML ( $_GET['id'] ) ); // kleine Info zur Vorstellung
        $theTemplate->tag ( "reservations", $reservations ); // Reservierungen
        $theTemplate->tag ( "sales", $sales ); // Verkäufe
        $theTemplate->tag ( "message", $message ); // Nachricht
        $theTemplate->tag ( "reset", $reset ); // Reset-Knop

         // Das Template ausgeben
        echo $theTemplate;
        
    } // END IF Buchungs-Übersicht für eine Vorstellung
    
    // Wochenübersicht über alle Vorstellungen anzeigen
    else {
        // Kino-Woche ermitteln (Kino-Woche von Do-Mi)
        // Wochen-Nummer entspricht normaler Kalender-Wochennummer
        // Außer: Wochentag ist Mo, Di oder Mi => dann Kalender-Wochennummer -1
        $this_day = date ( "w" ); // heutige Tag-Nummer aus PHP
        $this_week = date ( "W" ); // aktuelle Wochen-Nummer aus PHP
        makeKinoWeek ( &$this_week, $this_day ); // Falls der Wochetag Mo,Di,Mi ist

        // verändert die angezeigte Woche
        // Standard ist keine Veränderung, ansonsten lässt sich so leicht die nächste, übernächste, vorherige Woche etc. anzeigen
        if ( isset ( $_GET['add'] ) ) {
            settype ( $_GET['add'], "integer" );
            $additional_weeks = $_GET['add'];
        } else {
            $additional_weeks = 0;
        }
        // Setze die angezeigte Woche
        // Standard ist die aktuelle, aber über GET lässt sich jede Woche aufrufen
        if ( isset ( $_GET['week'] ) ) {
            settype ( $_GET['week'], "integer" );
            $show_week = $_GET['week'] + $additional_weeks;
        } else {
            $show_week = $this_week + $additional_weeks;
        }

        // Filme laden, die in dieser Woche laufen
        $movies_this_week = $sql->getData ( "running_weeks", "*", "WHERE `running_year` = '".date ( "Y" )."' AND `running_week` = '".$show_week."'" );
        $movies_this_week = ( $movies_this_week == FALSE ) ? array () : $movies_this_week;

        // Alle Filme die diese Woche laufen durchgehen
        $all_now_movies = "";
        foreach ( $movies_this_week as $movie ) {
            // HTML Ausgabe für Film laden
            $all_now_movies .= getMovieHTML ( $movie['movie_id'], TRUE );

            // Timetables laden
            $running_weeks = $sql->getData ( "running_weeks", "running_weeks_id", "WHERE `running_year` = '".date ( "Y" )."' AND `movie_id` = '".$movie['movie_id']."' AND `running_week` = '".$show_week."'", 0, TRUE );
            $movie['running_weeks_id'] = array (); // Array initialisieren
            foreach ( $running_weeks as $running_week ) { // Die IDs aus den einzelnen Showwochen laden
                $movie['running_weeks_ids'][] = $running_week['running_weeks_id'];
            }

            // HTML Ausgabe für Timetable laden
            $all_now_movies .= getTimetableHTML ( $movie['running_weeks_ids'], TRUE );
        }

        // Template für Jetzt im Kino-Seite laden
        $theTemplate = new Template ( "admin.tpl" );
        $theTemplate->load ( "SELECT_SHOW" );

        // Wochendaten ermitteln
        $week_data = getWeekData ( $show_week );
        $theTemplate->tag ( "start", $week_data[1][4] ); // Wochenstart
        $theTemplate->tag ( "end", $week_data[1][3] ); // Wochen-Ende
        $theTemplate->tag ( "number", $show_week ); // Wochen-Nummer
        $theTemplate->tag ( "last", "?go=admin&week=".($show_week-1) ); // URL zur letzten Woche
        $theTemplate->tag ( "next", "?go=admin&week=".($show_week+1) ); // URL zur nächsten Woche

        if ( $all_now_movies != "" ) {
            $theTemplate->tag ( "movies", $all_now_movies ); // Die Filme ins Template einbinden
        } else {
            // Movie-Templates laden
            $movieTemplate = new Template ( "movie.tpl" );
            $movieTemplate->load ( "NO_SHOWS" );
            $theTemplate->tag ( "movies", (string) $movieTemplate ); // keine Vorstellungen gefunden-Template einbinden
        }

        // Template ausgeben
        echo $theTemplate;
    } // END ELSE Vorstellungen Wochenübersicht

// Sonst Weiterleitung zur Login-Seite
} else {
    include ( ROOT_PATH."data/login.php");
}

// Dynamische Titel erweiterung setzen
$settings['title_ext'] = "Verwaltung";
?>
