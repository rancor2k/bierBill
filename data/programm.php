<?php
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
    $all_now_movies .= getMovieHTML ( $movie['movie_id'] );
    
    // HTML Ausgabe für Timetable laden
    $all_now_movies .= getTimetableHTML ( $movie['running_weeks_id'] );
}

// Template für Jetzt im Kino-Seite laden
$theTemplate = new Template ( "main.tpl" );
if ( $this_week == $show_week ) {
    $theTemplate->load ( "NOW" );
} else {
    $theTemplate->load ( "PROGRAM" );
}

// Wochendaten ermitteln
$week_data = getWeekData ( $show_week );
$theTemplate->tag ( "start", $week_data[1][4] ); // Wochenstart
$theTemplate->tag ( "end", $week_data[1][3] ); // Wochen-Ende
$theTemplate->tag ( "number", $show_week ); // Wochen-Nummer

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
$settings['title_ext'] = "Programm";
?>