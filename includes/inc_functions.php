<?php
// Haupt-Template laden (Alles was so um Body herumsteht und nicht variabel ist)
function getMainTemplate () {
    global $settings, $sql;

    // Haupt-Template
    $template = '{..doctype..}
<html lang="'.$settings['language'].'">
    <head>
        {..title..}{..meta..}{..link..}{..script..}
    </head>
    {..body..}
</html>';

    // Dynamischen Titel laden
    $template_title = getTitle ();

    // Metadaten laden
    $template_meta = '
                <meta name="title" content="'.$template_title.'">
                <meta name="description" content="'.$settings['description'].'">
                <meta name="keywords" lang="'.$settings['language'].'" content="'.$settings['keywords'].'">
                <meta name="robots" content="index,follow">
                <meta name="Revisit-after" content="3 days">
                
                <meta http-equiv="content-language" content="'.$settings['language'].'">
                <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
                
                <meta name="DC.Title" content="'.$template_title.'">
                <meta name="DC.Description" content="'.$settings['description'].'">
                <meta name="DC.Language" content="'.$settings['language'].'">
                <meta name="DC.Format" content="text/html">
    ';

    // link's + CSS einbinden
    $template_link = '
                <link rel="shortcut icon" href="style/icons/favicon.ico">'.getCSS ();

    // Platzhalter ersetzen
    $template = str_replace("{..title..}", "<title>".$template_title."</title>", $template);
    $template = str_replace("{..meta..}", $template_meta, $template);
    $template = str_replace("{..link..}", $template_link, $template);
    $template = str_replace("{..script..}", getJS (), $template);

    // Haupt-Template zurückgeben
    return $template;
}

// Dynamischen Titel generieren
function getTitle () {
    global $settings;

    if ( trim ( $settings['title_ext'] ) != "" ) { // Falls eine Erweiterung zum normalen Titel existiert
        $ext_title = str_replace ( "{..title..}", $settings['title'], $settings['title_format'] ); // Lade das Schema und setze den Titel ein
        $ext_title = str_replace ( "{..extension..}", $settings['title_ext'], $ext_title ); // sowie die Erweiterung
        return $ext_title;
    } else {
        return $settings['title']; // Sonst nur den normalen Titel zurückgeben
    }
}

// CSS Dateien laden
function getCSS () {

    // Liste der CSS-Dateien auslesen
    $files = scandirExtension ( ROOT_PATH."style/", "css" );

    // Array für spezielle CSS-Dateien definieren
    // import.css => nur diese Datei wird importiert
    // noscript.css => wird importiert, außer JS ist aktiv
    $files_special = array ( "import.css", "noscript.css" );

    // Spezielle CSS-Dateien aus Datei-Liste filtern
    $files_without_special = array_diff ( $files, $files_special );

    // Zurücksetzten aus Sicherheitsgründen
    unset ( $template_css );

    // Wenn import.css existiert, nur dieses einbinden
    if ( in_array ( "import.css", $files ) ) {
        $template_css .= '
                <link rel="stylesheet" type="text/css" href="style/import.css">';
    // Sonst alle normalen CSS-Dateien einbinden
    } else {
        foreach ( $files_without_special as $aFile ) {
            $template_css .= '
                <link rel="stylesheet" type="text/css" href="style/"'.$aFile.'">';
        }
    }

    // noscript.css einbinden, falls es existiert
    if ( in_array ( "noscript.css", $files ) ) {
        $template_css .= '
                <link rel="stylesheet" type="text/css" id="noscriptcss" href="style/noscript.css">';
    }

    // HTML zurückgeben
    return $template_css;
}

// Javascript Dateien laden
function getJS () {

    // Liste der JS-Dateien auslesen
    $files = scandirExtension ( ROOT_PATH."style/", "js" );

    // HTML zusammenbauen
    $template_js = '
                <script type="text/javascript" src="external/jquery.tools.min.js"></script>
                <script type="text/javascript" src="includes/inc_functions.js"></script>';
    foreach ( $files as $aFile ) {
        $template_js .= '
                <script type="text/javascript" src="style/"'.$aFile.'"></script>';
    }

    // HTML zurückgeben
    return $template_js;
}

// Inhalt laden
function getContent ( $GOTO ) {
    global $settings, $sql;

    // Ausgabe buffern
    ob_start();
    // Script in /data/
    if ( file_exists ( ROOT_PATH."data/".$GOTO.".php" ) ) {
        include ( ROOT_PATH . "data/".$GOTO.".php" );
    // HTML  in /data/
    } elseif ( file_exists ( ROOT_PATH."data/".$GOTO.".html" ) ) {
        include ( "data/".$GOTO.".html" );

    // 404-Fehler-Seite
    } else {
        $settings['goto'] = "404";
        include ( "data/404.php" );
    }
    $content = ob_get_contents(); // Ausgabe in Variable speichenr
    ob_end_clean(); // Buffer leeren

    // Content zurückgeben
    return $content;
}

// Ziel sichern und ermitteln, falls unbekannt
function getGoTo ( $GO ) {
    global $settings;
    
    // $_GET['go'] sichern und überprüfen
    if ( !isset( $GO ) || $GO == "" ) {
        $GO = "start"; // Standard-Seite
    }
    $goto = secureSQL ( $GO ) ;

    // write $goto into $global_config_arr['goto']
    $settings['goto'] = $GO;
}

// HTML Ausgabe eines Films Laden
function getMovieHTML ( $ID, $MINI = FALSE ) {
    global $sql;

    // Template laden
    $theTemplate = new Template ( "movie.tpl" );
    if ( !$MINI ) {
        $theTemplate->load ( "MOVIE" );
    } else {
        $theTemplate->load ( "MOVIE_MINI" );
    }
    
    // Film Daten laden
    $movie_data = $sql->getData ( "movies", "*", "WHERE `movie_id` = '".$ID."'", 1 );

    // Tags umsetzen
    $theTemplate->clearTags ();
    $theTemplate->tag ( "title", $movie_data['movie_title'] );
    $theTemplate->tag ( "text", $movie_data['movie_text'] );
    $theTemplate->tag ( "director", $movie_data['movie_director'] );
    $theTemplate->tag ( "actors", $movie_data['movie_actors'] );
    $theTemplate->tag ( "year", $movie_data['movie_year'] );
    $theTemplate->tag ( "length", $movie_data['movie_length'] );
    $theTemplate->tag ( "type", $movie_data['movie_type'] );
    
    // Extra Gebühren anzeigen
    $extra_charge = array ();
    if ( $movie_data['movie_extra_charge_3d'] == 1 ) {
        $extra_charge[] = "Aufpreis 3D-Film";
    }
    if ( $movie_data['movie_extra_charge_length'] == 1 ) {
        $extra_charge[] = "Aufpreis Überlänge";
    }
    $theTemplate->tag ( "extra_charge", implode ( ", ", $extra_charge ) );

    // Titel aufs wesentliche reduzieren, damit Bild als URL eingelesen werden kann
    $compressed_title = explode ( " ", compressText ( $movie_data['movie_title'] ) );
    $theTemplate->tag ( "img_url", "media/movies/".$movie_data['movie_id']."_".implode ( "_", $compressed_title ).".jpg" );

    // HTML zurück geben
    return (string) $theTemplate;
}

// HTML Ausgabe einer Vorstellung laden
function getShowInfoHTML ( $SHOW_ID ) {
    global $sql;

    settype ( $SHOW_ID, "integer" );

    // Template laden
    $theTemplate = new Template ( "booking.tpl" );

    // Show, Film und Kino Daten laden
    $show_data = mysql_fetch_assoc ( $sql->query ( "SELECT * FROM {..pref..}running_weeks W, {..pref..}running_table T WHERE T.`show_id` = '".$SHOW_ID."' AND T.`running_weeks_id` = W.`running_weeks_id`") );
    $movie_data = $sql->getData ( "movies", "*", "WHERE `movie_id` = '".$show_data['movie_id']."'", 1 );
    $screen_data = $sql->getData ( "screens", "*", "WHERE `screen_id` = '".$show_data['screen_id']."'", 1 );

    // Show Info erstellen
    $real_week = $show_data['running_week'];
    makeRealWeek ( $real_week, $show_data['running_day'] );
    $showtimestamp = strtotime ( date ( "Y-m-d", timeFromYWD ( $show_data['running_year'], $real_week, $show_data['running_day'] ) ) ." ". $show_data['running_time'] );

    $theTemplate->load ( "BOOKING_SHOW_INFO" );
    $theTemplate->clearTags ();
    $theTemplate->tag ( "movie_title", $movie_data['movie_title'] );
    $theTemplate->tag ( "show_date", date ( "d.m.y", $showtimestamp ) );
    $theTemplate->tag ( "show_time", date ( "H:i", $showtimestamp ) );
    $theTemplate->tag ( "screen_name", $screen_data['screen_name'] );
    return (string) $theTemplate;
}

// HTML Ausgabe einer Timetable erstellen
function getTimetableHTML ( $RUNNING_IDS, $ADMIN = FALSE ) {
    global $sql;

    // Kinowochen der Running IDS ermitteln
    if ( !is_array ( $RUNNING_IDS ) ) {
        $RUNNING_IDS = array ( $RUNNING_IDS ); // Bei nur einer einzigen Running ID muss nicht extra ein Array übergeben werden
    }
    $kino_weeks = $sql->getData ( "running_weeks", "*", "WHERE `running_weeks_id` IN(".implode ( ",", $RUNNING_IDS ).") ORDER BY `running_week`" );
    $kino_weeks = ( $kino_weeks == FALSE ) ? array() : $kino_weeks; // Fehler abfangen, wenn keine running week gefunden wird

    // Template laden
    $theTemplate = new Template ( "movie.tpl" );

    // Für jede Woche durchgehen
    $all_show_weeks = "";
    foreach ( $kino_weeks as $kino_week ) {
        // Wochendaten laden
        $week_data = getWeekData ( $kino_week['running_week'] );

        // Spielzeiten laden
        $showtimes = $sql->getData ( "running_table", "*", "WHERE `running_weeks_id` = '".$kino_week['running_weeks_id']."' ORDER BY `running_day`, `running_time`" );
        $showtimes = ( $showtimes == FALSE ) ? array () : $showtimes;

        // Alle Auführungen der Woche durchgehen
        $times = array ( 0 => array(), 1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array(), 6 => array() ); // Times Array initialisieren
        foreach ( $showtimes as $show ) {
            // Timestamp der Auffühung
            $showtimestamp = strtotime ( date ( "Y-m-d", $week_data[0][$show['running_day']] ) . $show['running_time'] );

            // Unterscheidung zw. normaler und Admin ausgabe
            if ( $ADMIN ) {
                $theTemplate->load ( "SHOW" );
                $theTemplate->clearTags ();
                $theTemplate->tag ( "url", "?go=admin&id=".$show['show_id'] );
            } else {
                // Showtime Template laden und Tags umsetzen
                if ( $showtimestamp >= time() + 60*45 ) {
                    $theTemplate->load ( "SHOW" );
                    $theTemplate->clearTags ();
                    $theTemplate->tag ( "url", "?go=booking&id=".$show['show_id'] );
                } else {
                    $theTemplate->load ( "SHOW_OLD" );
                    $theTemplate->clearTags ();
                }
            }
            $theTemplate->tag ( "time", date ( "H:i", $showtimestamp ) );
            $times[$show['running_day']][] = (string) $theTemplate;
        }
        // Showtime Separator laden
        $theTemplate->load ( "SHOW_SEPARATOR" );
        $theTemplate->clearTags ();
        $theSeparator = (string) $theTemplate;

        // Timetable Template laden und Tags umsetzen
        $theTemplate->load ( "SHOW_WEEK" );
        $theTemplate->clearTags ();
        $theTemplate->tag ( "start", $week_data[1][4] );
        $theTemplate->tag ( "end", $week_data[1][3] );

        $theTemplate->tag ( "so_times", implode ( $theSeparator, $times[0] ) );
        $theTemplate->tag ( "mo_times", implode ( $theSeparator, $times[1] ) );
        $theTemplate->tag ( "di_times", implode ( $theSeparator, $times[2] ) );
        $theTemplate->tag ( "mi_times", implode ( $theSeparator, $times[3] ) );
        $theTemplate->tag ( "do_times", implode ( $theSeparator, $times[4] ) );
        $theTemplate->tag ( "fr_times", implode ( $theSeparator, $times[5] ) );
        $theTemplate->tag ( "sa_times", implode ( $theSeparator, $times[6] ) );

        $theTemplate->tag ( "so_date", $week_data[1][0] );
        $theTemplate->tag ( "mo_date", $week_data[1][1] );
        $theTemplate->tag ( "di_date", $week_data[1][2] );
        $theTemplate->tag ( "mi_date", $week_data[1][3] );
        $theTemplate->tag ( "do_date", $week_data[1][4] );
        $theTemplate->tag ( "fr_date", $week_data[1][5] );
        $theTemplate->tag ( "sa_date", $week_data[1][6] );

        // Show Week hinten dran hängen
        $all_show_weeks .= (string) $theTemplate;
    }
    
    // Timetable Template laden
    $theTemplate->load ( "TIMETABLE" );
    $theTemplate->clearTags ();
    $theTemplate->tag ( "show_weeks", $all_show_weeks );
    
    // HTML zurückgeben
    return(string) $theTemplate;
}

// Ermittelt alle Daten einer Kino-Woche
function getWeekData ( $KINO_WEEK ) {

    // Wochendaten ermitteln
    for ( $i=0; $i<=6; $i++ ) { // Alle Spieltage durchgehen
        $real_week = $KINO_WEEK;
        makeRealWeek ( &$real_week, $i ); // Falls Tag Mo,Di,Mi => aus Kino-Woche normale Kalender Woche zu machen
        $timestamps[$i] = timeFromYWD ( date ( "Y" ), $real_week, $i ); // Timestamp ermitteln
        $dates[$i] = date ( "j.n.y", $timestamps[$i] ); // Ausgabe formatieren ermitteln
    }
    
    return array ( $timestamps, $dates );
}

// Macht aus einer Kino Woche eine normale Woche
function makeRealWeek ( &$KINO_WEEK, $DAY ) {
    if ( $DAY >= 1 && $DAY <= 3 ){
        $KINO_WEEK++; // Falls Tag Mo,Di,Mi => aus Kino-Woche normale Kalender Woche machen
    }
}
// Macht aus einer normale Woche eine Kino Woche
function makeKinoWeek ( &$REAL_WEEK, $DAY ) {
    if ( $DAY >= 1 && $DAY <= 3 ){
        $REAL_WEEK--; // Falls Tag Mo,Di,Mi => aus normale Kalender Woche eine Kino-Woche machen
    }
}

// Verzeichnis nach Dateien mit bestimmter Endung durchsuchen
function scandirExtension ( $FOLDER, $FILE_EXT ) {
    $files = scandir ( $FOLDER ); // Datei-Liste laden
    $file_names = array(); // neue Liste initialisieren
    foreach ( $files as $aFile ) { // Jede Datei durchgehen
        if ( pathinfo ( $aFile, PATHINFO_EXTENSION ) == $FILE_EXT ) { // Endung entspricht Vorgabe
            $file_names[] = $aFile;
        }
    }
    return $file_names;
}

// SQL-Abfragen absichern
function secureSQL ( $CONTENT ) {
    global $sql;

    $CONTENT = mysql_real_escape_string ( unquote ( $CONTENT ), $sql->getRes() ); // String erst von evtl. unnötigen magic quote Slashes befreien und dann escapen
    return $CONTENT;
}

// Schmeißt alles raus was in Formular-Feldern das HTML durcheinander wirbereln könnte
function killHTML ( $CONTENT ) {
    $CONTENT = htmlspecialchars ( unquote ( $CONTENT ), ENT_QUOTES );
    return $CONTENT;
}

// entfernt quotes die evtl. durch magic quote gesetzt wurden
function unquote ( $CONTENT ) {
    if ( get_magic_quotes_gpc () ) { // Falls magic quote gesetzt ist
        $CONTENT = stripslashes ( $CONTENT ); // Dann werden Slashes entfernt
    }
    return $CONTENT;
}

// Berechnet einen Timestamp aus den Angaben Jahr, Kalenderwoche, Wochentag-Nummer
function timeFromYWD ( $Y, $W, $D ) {
    $first_in_year = mktime ( 12, 0, 0, 1, 1, $Y ); // Gehe aus vom 1.1. des Jahres aus (Mittags um evtl. Sommerzeit Probleme zu umgehen)
    $timestamp = $first_in_year + 60*60*24*7*$W; // Addiere die Wochen hinzu
    
    $first_in_year_n = date ( "N", $first_in_year ); // ausgleichen, dass 1.1. nicht immer Montag ist
    $timestamp = $timestamp - 60*60*24*($first_in_year_n-1); // entsprechende Stundenzahl abziehen
    $D = ( $D == 0 ) ? 7 : $D; // Falls der angegebene Tag ein als 0 dargestellter Sonntag ist => setze Sonntag = 7
    $timestamp = $timestamp + 60*60*24*($D-1); // die in der Woche vergangenen Tage wirder dazu addieren
    return $timestamp;
}

// formatiert einen Zeitstring um
function strtotimestr ( $FORMAT, $TIMESTR ) {
    return date ( $FORMAT, strtotime ( $TIMESTR ) );
}


// Text Daten aufs wesentliche vereinheitlichen
// Quelle:
// Aus dem Code des Projekts "Frogsystem 2 [http://www.frogsystem.de/]"
// allerdings habe ich das dafür auch irgendwo aus dem Netz gezogen, aber woher?
function compressText ( $TEXT ) {
    $locSearch[] = "=ß=i"; // ersetzt ß durch ss
    $locSearch[] = "=ä|Ä=i"; // ersetzt ä durch ae
    $locSearch[] = "=ö|Ö=i"; // ersetzt ö durch oe
    $locSearch[] = "=ü|Ü=i"; // ersetzt ü durch ue
    $locSearch[] = "=á|à|â|Â|Á|À=i"; // ersetzt alle möglichen As durch a
    $locSearch[] = "=ó|ò|ô|Ô|Ó|Ò=i"; // ersetzt alle möglichen Os durch o
    $locSearch[] = "=ú|ù|û|Û|Ú|Ù=i"; // ersetzt alle möglichen Us durch u
    $locSearch[] = "=é|è|ê|Ê|É|È|ë=i"; // ersetzt alle möglichen Es durch e
    $locSearch[] = "=í|ì|î|Î|Í|Ì|ï=i"; // ersetzt alle möglichen Is durch i
    $locSearch[] = "=ñ=i"; // ersetzt alle möglichen Ns durch n
    $locSearch[] = "=ç=i"; // ersetzt alle möglichen Cs durch c
    $locSearch[] = "=([^A-Za-z0-9])="; // ersetzt alles was noch nicht Buchstabe oder Zahl ist durch Space
    $locSearch[] = "= +="; // ersetzt alles was noch nicht Buchstabe oder Zahl ist durch Space

    $locReplace[] = "ss"; // ersetzt ß durch ss
    $locReplace[] = "ae"; // ersetzt ä durch ae
    $locReplace[] = "oe"; // ersetzt ö durch oe
    $locReplace[] = "ue"; // ersetzt ü durch ue
    $locReplace[] = "a"; // ersetzt alle möglichen As durch a
    $locReplace[] = "o"; // ersetzt alle möglichen Os durch o
    $locReplace[] = "u"; // ersetzt alle möglichen Us durch u
    $locReplace[] = "e"; // ersetzt alle möglichen Es durch e
    $locReplace[] = "i"; // ersetzt alle möglichen Is durch i
    $locReplace[] = "n"; // ersetzt alle möglichen Ns durch n
    $locReplace[] = "c"; // ersetzt alle möglichen Cs durch c
    $locReplace[] = " "; // ersetzt alles was noch nicht Buchstabe oder Zahl ist durch Space
    $locReplace[] = " "; // ersetzt alles was noch nicht Buchstabe oder Zahl ist durch Space

    $TEXT = trim ( strtolower ( stripslashes ( $TEXT ) ) ); // alles in Kleinbuchstaben wandeln
    $TEXT = preg_replace ( $locSearch, $locReplace, $TEXT ); // nicht gewollte Buchstaben ersetzen
    return $TEXT;
}

// Formular Daten auf Plausibilität überprüfen
function checkFormData ( $DATA, $TYPE, $REQUIRED = FALSE, $PATTERN = FALSE ) {
    $DATA = trim ( $DATA );

    // Wenn Pflichtfeld und nicht angegben
    if ( $REQUIRED && ( !isset ( $DATA ) || $DATA == "" ) ) {
        return FALSE;
    }

    // Feld Arten switchen
    switch ( $TYPE ) {
        case "email": // Kein zusäzliches Pattern erlaubt
            // Quelle: http://fightingforalostcause.net/misc/2006/compare-email-regex.php
            $regexp = '/^([\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+\.)*[\w\!\#$\%\&\'\*\+\-\/\=\?\^\`{\|\}\~]+@((((([a-z0-9]{1}[a-z0-9\-]{0,62}[a-z0-9]{1})|[a-z])\.)+[a-z]{2,6})|(\d{1,3}\.){3}\d{1,3}(\:\d{1,5})?)$/i';
            if ( preg_match ( $regexp, $DATA ) ){
                return TRUE;
            }
            break;
        case "text": // gegen Pattern checken
            if ( !$OPTIONS || preg_match ( $PATTERN, $DATA ) ) {
                return TRUE;
            }
            break;
    }
    // weitere Daten-Arten (z.B. URL, Zahl...) könnten implementiert werden, werden hier aber nicht benötigt
    
    
    // sonst
    return FALSE;
}

// Läd alle bereits gekauften, reservierten oder derzeit blockierten Plätze (Ausnahme: für den User mit der angebenen ID blockierte Plätze, diese werden nicht geladen, weil es ja "seine" sind)
function getTicketData ( $SHOW_ID, $USER_ID ) {
    global $sql;
    
    // Vor Injections schützen
    settype ( $SHOW_ID, "integer" );
    settype ( $USER_ID, "integer" );

    // Array initialisieren
    $reservations = array ();
    $sold_tickets = array ();
    $blocked_seats = array ();
    
    // Tickets (d.h. gekauft od. reserviert) laden
    $ticket_data = $sql->getData ( "tickets", "*", "WHERE `show_id` = '".$SHOW_ID."'" );
    $ticket_data = $sql->wasGetSuccessful ( $ticket_data ) ? $ticket_data : array ();

    // Ticket Daten durchgehen
    foreach ( $ticket_data as $aBooking ) {
        $found_seats = explode ( ",", $aBooking['ticket_seats'] );
        if ( $aBooking['ticket_sold'] == 1 ) { // Unterscheidung gekauft <=> reserviert
            $sold_tickets = array_merge ( $sold_tickets, $found_seats );
        } else {
            $reservations = array_merge ( $reservations, $found_seats );
        }
    }
    
    // Abgelaufene Blockierungen löschen
    $sql->deleteData ( "blocked", "`blocked_time` <= ".(time()-90)."");
    
    // blockierte Sitze laden
    $blocked_data = $sql->getData ( "blocked", "*", "WHERE `show_id` = '".$SHOW_ID."' AND `user_id` != '".$USER_ID."'" );
    $blocked_data = $sql->wasGetSuccessful ( $blocked_data ) ? $blocked_data : array ();

    // Blockierungen durchgehen
    foreach ( $blocked_data as $aBlock ) {
        $found_seats = explode ( ",", $aBlock['blocked_seats'] );
        $blocked_seats = array_merge ( $blocked_seats, $found_seats );
    }
    
    // alle 3 Listen zurückgeben
    return array ( $reservations, $sold_tickets, $blocked_seats );
}
?>
