<?php
// Demnächst-Filme aus der Tabelle laden
$next_movies = $sql->getData ( "next", "*", "ORDER BY `next_position`" );
$next_movies = ( $next_movies == FALSE ) ? array () : $next_movies;

// Für alle Filme die Film-Daten laden und ausgeben
$all_next_movies = "";
foreach ( $next_movies as $movie ) {
    // HTML Ausgabe für Film laden
    $all_next_movies .= getMovieHTML ( $movie['movie_id'] );
}


// Template für Vorschau-Seite laden
$theTemplate = new Template ( "main.tpl" );
$theTemplate->load ( "NEXT" );
$theTemplate->tag ( "movies", $all_next_movies ); // Die Filme ins Template einbinden

// Template ausgeben
echo $theTemplate;

// Dynamische Titel erweiterung setzen
$settings['title_ext'] = "Demnächst im High Noon";
?>