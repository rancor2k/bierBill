<?php
// Aktuelle Kinowoche
$show_week =  date ( "W" );
makeKinoWeek ( &$show_week, date ( "w" ) );

// Filme laden, die in dieser Woche laufen
$movies_this_week = $sql->getData ( "running_weeks", "*", "WHERE `running_year` = '".date ( "Y" )."' AND `running_week` = '".$show_week."'" );

// Zufälligen Film auswählen
if ( $sql->wasGetSuccessful ( $movies_this_week ) ) {
    $random_number = rand ( 0, count ( $movies_this_week )-1 );  // Zufallszahl
    $random_movie = getMovieHTML ( $movies_this_week[$random_number]['movie_id'] ); // Array Eintrag nach Zufallszahl auswählen
} else {
    $random_movie = "";
}

// Template laden und ausgeben
$theTemplate = new Template ( "main.tpl" );
$theTemplate->load ( "START" );
$theTemplate->tag ( "movie", $random_movie );
echo $theTemplate;

?>