<?php
// Header Information setzen
header("Status: 404 Not Found", true, 404);

// Fehlermeldung mit Hilfe eines Heredocs ausgeben
echo <<< HERE
<h3>Es trat ein Fehler auf</h3>

<h5>Fehlercode 404</h5>
<p>Das angeforderte Dokument konnte leider nicht gefunden werden.</p>
HERE;

// Dynamische Titel erweiterung setzen
$settings['title_ext'] = "Fehler 404";
?>