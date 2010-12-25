<?php
// Header Information setzen
header("Status: 403 Forbidden", true, 403);

// Fehlermeldung mit Hilfe eines Heredocs ausgeben
echo <<< HERE
<h3>Es trat ein Fehler auf</h3>

<h5>Fehlercode 403</h5>
<p>Keine Zugriffsrechte für diese Seite.</p>
HERE;

// Dynamische Titel erweiterung setzen
$settings['title_ext'] = "Fehler 403";
?>