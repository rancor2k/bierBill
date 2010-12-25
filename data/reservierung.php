<?php
// Kino-Woche ermitteln (Kino-Woche von Do-Mi)
// Wochen-Nummer entspricht normaler Kalender-Wochennummer
// Außer: Wochentag ist Mo, Di oder Mi => dann Kalender-Wochennummer -1
$this_day = date ( "w" ); // heutige Tag-Nummer aus PHP
$this_week = date ( "W" ); // aktuelle Wochen-Nummer aus PHP
makeKinoWeek ( &$this_week, $this_day ); // Falls der Wochetag Mo,Di,Mi ist

// Filme laden, die in dieser Woche laufen
$movies_this_week = $sql->getData ( "running_weeks", "movie_id", "WHERE `running_year` = '".date ( "Y" )."' AND (`running_week` = '".$this_week."' OR `running_week` = '".($this_week+1)."' )", 0, TRUE );
$movies_this_week = ( $movies_this_week == FALSE ) ? array () : $movies_this_week;

// Alle Filme die diese Woche laufen durchgehen
$all_now_movies = "";
foreach ( $movies_this_week as $movie ) {
    // HTML Ausgabe für Film laden
    $all_now_movies .= getMovieHTML ( $movie['movie_id'], TRUE );
    
    // Timetables laden
    $running_weeks = $sql->getData ( "running_weeks", "running_weeks_id", "WHERE `running_year` = '".date ( "Y" )."' AND `movie_id` = '".$movie['movie_id']."' AND (`running_week` = '".$this_week."' OR `running_week` = '".($this_week+1)."' )", 0, TRUE );
    $movie['running_weeks_id'] = array (); // Array initialisieren
    foreach ( $running_weeks as $running_week ) { // Die IDs aus den einzelnen Showwochen laden
        $movie['running_weeks_ids'][] = $running_week['running_weeks_id'];
    }
    
    // HTML Ausgabe für Timetable laden
    $all_now_movies .= getTimetableHTML ( $movie['running_weeks_ids'] );
}

// Template für Jetzt im Kino-Seite laden
$theTemplate = new Template ( "main.tpl" );
$theTemplate->load ( "RESERVATION" );

// Wochendaten ermitteln
$week_data = getWeekData ( $this_week );
$theTemplate->tag ( "start", $week_data[1][4] ); // Wochenstart
$theTemplate->tag ( "end", $week_data[1][3] ); // Wochen-Ende
$theTemplate->tag ( "number", $this_week ); // Wochen-Nummer

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

// Dynamische Titel erweiterung setzen
$settings['title_ext'] = "Online-Reservierung";
?>