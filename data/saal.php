<?php
    // Saal Daten aus DB laden
    $screen_data = $sql->getData ( "screens", "*", "WHERE `screen_id` = '1'", 1 );
    $screen = new ScreenRoom ( $screen_data['screen_seats'] );

    // Template laden und ausgeben
    $theTemplate = new Template ( "main.tpl" );
    $theTemplate->load ( "SCREENROOM" );
    $theTemplate->tag ( "screen", $screen );
    echo $theTemplate;
?>