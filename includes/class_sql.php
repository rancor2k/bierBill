<?php
// Klasse zum flexiblen Zugriff auf eine Datenbank (kann bei Bedarf an andere Datenbanken angepasst werden)
// Variante für MySQL
// Interface wurde der Einfachheit halber erstmal weggelassen

// Quelle:
// Basiert auf Code aus dem Projekt "Frogsystem 2 [http://www.frogsystem.de/]"
// Ursprünglicher Hauptautor: "Satans Krümelmonster" (Pseudonym), Co-Autor: Moritz "Sweil" Kornher
// für das Kino-Projekt komplett überarbeitet und angepasst von Moritz Kornher

class SQL {

    private $sql; // Die Verbindungs-Resource
    private $pref; // Tabellen-Präfix (siehe inc_login.php)
    private $error; // evtl. aufgetretene SQL-Fehler
    private $qrystr; // TODO

    // Der Konstrukter
    // Speichert Die SQL-Verbindung, den Datenbank-Namen und das Präfix zur spätern Verwendung und stellt eine Verbindung her
    public function __construct ( $host, $data, $user, $pass, $pref ) {
        $this->sql = @mysql_connect ( $host, $user, $pass );
        if ( $this->sql && mysql_select_db ( $data, $this->sql ) ) {
            $this->db = $data;
            $this->pref = $pref;
        } else {
            $this->sql = null; // Bei Fehler auf NULL setzen (NULL kann leicht abgefragt werden)
        }
    }

    //Gibt die gespeicherte MySQL-Ressource zurück
    public function getRes () {
        if ( isset ( $this->sql ) && $this->sql !== null ) {
            return $this->sql;
        } else {
            return FALSE;  // Und wenn es keine gibt, gibt die Funktion FALSE zurück
        }
    }

    // Gibt einen Array mit evtl. aufgetretenem Fehler zurück
    public function getError () {
        if ( isset ( $this->error ) && $this->error !== null ) {
            return $this->error;
        } else {
            return FALSE;
        }
    }

    // Gibt den gespeicherten Query-String zurück
    public function getQueryString () {
        if ( isset ( $this->qrystr ) && $this->qrystr !== null ) {
            return $this->qrystr;
        } else {
            return FALSE;
        }
    }

    // Führt den gespeicherten Query-String tatsächlich aus
    private function doQuery () {
        if ( !isset ( $this->qrystr ) ) { // Abbruch, wenn kein Query-String gespeichert ist
            return FALSE;
        }

        $theQuery = mysql_query ( $this->qrystr, $this->sql ); // Query ausführen
        unset( $this->qrystr, $this->error ); // QueryString und Fehler-Array zurücksetzen
        if ( mysql_error ( $this->sql ) !== "" ) { // Falls ein Fehler auftritt
            $this->error[0] = mysql_errno ( $this->sql ); // Fehler Nummer
            $this->error[1] = mysql_error ( $this->sql ); // Text-Beschreibung des Fehlers
            return FALSE;
        } else {
            return $theQuery;
        }
    }

    // Eine beliebige Abfrage
    public function query ( $qrystr ){
        $this->qrystr = str_replace ( "{..pref..}", $this->pref, $qrystr ); // {..pref..} wird automatisch ersetzt, so dass flexibel programmiert werden kann
        return $this->doQuery();
    }

    // Gibt die letzte Insert-ID zurück
    public function getInsertId (){
        if ( !$this->getRes () ) {
            return FALSE;
        }
        return mysql_insert_id ( $this->sql );
    }

    // Eine SELECT-Abfrage ausführen
    // $addititional = 0 => Normal; $addititional = 1 => nur die erste Ergebnis-Zeile; $addititional = 2 => nur Anzahl der Ergebnis-Zeilen
    public function getData ( $table, $row, $optional = "", $addititional = 0, $distinct = FALSE ) {
        // Fehler und Query zurücksetzen
        unset ( $this->error, $this->qrystr );

        // SELECT-Abfrage mit DISTINCT Attribut oder nicht
        $select = ( $distinct ) ? "SELECT DISTINCT " : "SELECT ";

        // Querystring aufbauen
        $qrystr = $select . $row . " FROM `" . $this->pref . $table . "`";

        // evtl. Optionale Angaben (WHERE, LIMT, etc.) anhängen
        if ( !empty ( $optional ) ) {
        $qrystr .= " ".$optional;
        }

        // Query-String in Objekt ablegen
        $this->qrystr = $qrystr;

        // Query durchführen
        $qry = mysql_query ( $qrystr, $this->sql );

        // Wenn Fehler auftreten
        if ( mysql_error ( $this->sql ) !== "" ) {
            // Fehlerdaten speichern
            $this->error[0] = mysql_errno($this->sql);
            $this->error[1] = mysql_error($this->sql);
            return FALSE;

        // Keine Fehler
        } else {
            // Wenn nur nach der Anzahl der Ergebnis-Zeilen gefragt ist
            // oder wenn keine passenden Zeilen gefunden wurden
            if ( mysql_num_rows ( $qry ) == 0 || $addititional == 2 ) {
                return mysql_num_rows ( $qry );
            }

            // Die ganzen Ergebnisse laden
            $ret = array();
            while( $erg = mysql_fetch_assoc ( $qry ) ) {
                $ret[]= $erg;
            }
            
            // Art der Rückgabe (also $addititional) durchswitchen
            switch ( $addititional ) {
                case 0: // Standard-Rückgabe
                    return $ret;
                    break;
                case 1: // nur die erste Ergebnis-Zeile
                    if ( count ( $ret[0] ) === 1 ) { // Wenn das Ergebnis nur aus einem Wert besteht, wird dieser direkt zurückgeben und nicht als Array
                        $keys = array_keys ( $ret[0] ) ; // Entsprechend wird hier der 1. Key des Arrays ermittelt
                        return $ret[0][$keys[0]]; // Und anschließend zur Rückgabe verwendet
                    } else {
                        return $ret[0]; // Die erste Ergebnis-Zeile rückgeben
                    }
                    break;
                // Fall 2 wurde schon weiter oben abfangen
            }
        }
    }

    // Eine Insert-Anweisung durchführen
    public function setData ( $table, $cols, $values ) {
        // Daten für Spalten und Werte laden
        // Können jeweils Komma-getrennte-Werte (CSV) oder Arrays sein
        // Anwendung ist meistens so, dass die Spalten als CSV angeben werden und die Werte als Array => ist die kürzeste Notation
        // Wenn aber z.B. nur 2 Zahlen als Wert übergeben werden, dann kann man sinnigerweise auch hier auf die CSV-Notation zurückgreifen
        $cols   = ( is_array ( $cols ) ) ? $cols : explode ( ",", $cols );
        $values = ( is_array ( $values ) ) ? $values : explode ( ",", $values );

        // Darf natürlich nur soviele Spalten wie Werte geben
        if ( count ( $cols ) !== count ( $values ) || count ( $cols ) === 0 ) {
            return FALSE;
        }
        
        // Daten für Query vorbereiten
        $this->arraytrim ( $cols );
        $this->arraytrim ( $values );
        $cols   = "`" . implode ( "`, `", $cols ) . "`";
        $values = "'" . implode ( "', '", $values ) . "'";

        // Query-String aufbauen ...
        $this->qrystr = "INSERT INTO `".$this->pref.$table."` (".$cols.") VALUES (".$values.")";
        return $this->doQuery(); // ... und ausführen
    }

    // Eine Update-Anweisung durchführen
    public function updateData ( $table, $cols, $values, $additional = "" ) {
        // Daten für Spalten und Werte laden und vorbereiten
        // Prinzip ist das selbe wie bei setData
        $cols   = ( is_array ( $cols ) ) ? $cols : explode ( ",", $cols );
        $values = ( is_array ( $values ) ) ? $values : explode ( ",", $values );
        
        // Darf natürlich nur soviele Spalten wie Werte geben
        if ( count ( $cols ) !== count ( $values ) || count ( $cols ) === 0 ) {
            return FALSE;
        }
        
        // Daten für Query vorbereiten
        $this->arraytrim ( $cols );
        $this->arraytrim ( $values );
        
        // Query-String aufbauen ...
        $qrystr = "UPDATE `".$this->pref.$table."` SET ";
        for ( $i = 0; $i < count ( $cols ) ; $i++ ) { // jeden Eintrag durchgehen
            $qrystr .= "`".$cols[$i]."` = '".$values[$i]."'"; // entsprechend an den Query-String dranhängen
            if ( $i != count($cols)-1 ) { // Falls nicht der letzte Eintrag...
                $qrystr .= ", "; // ... muss ein Komma eingefügt werden
            }
        }

        // Query-String fertig bauen mit der obligatorischen Erweiteung für z.b. WHERE oder LIMIT
        $qrystr .= ( $additional != "" ) ? " ".$additional : "";
        $this->qrystr = $qrystr;
        return $this->doQuery(); // ... und ausführen
    }

    // Eine Delete-Anweisung durchführen
    public function deleteData ( $table, $conditions = "", $additional = "" ) {
        // Bedinungen ($conditions) sind hier Pflicht, damit nicht ausversehen alle Daten gelöscht werden
        // $additional bezieht sich dementsprechend nur noch auf LIMIT o.ä.
        $qrystr = "DELETE FROM `".$this->pref.$table."`";
        $qrystr .= ( $conditions != "" ) ? " WHERE ".$conditions : "";
        $qrystr .= ( $additional != "" ) ? " ".$additional : "";

        $this->qrystr=$qrystr;
        return $this->doQuery();
    }

    // Funktion die prüft ob eine getData-Abfrage im umgangsprachlichen Sinne erfolgreich war, d.h. dass Sie min. ein Ergebnis liefert und keinen Fehler
    // Könnte man natürlich auch als "normale" Funktion in die inc_functions.php ausgliedern, passt m.M.n. aber thematisch besser hierzu
    public function wasGetSuccessful ( $DATA_RETRUN, $ZERO_ROWS_IS_OK = FALSE ) {
        return ( $DATA_RETRUN != FALSE && ( $DATA_RETRUN != 0 | $ZERO_ROWS_IS_OK ) ); // Das übergebene Daten-Packet darf eben nicht == 0 oder == FALSE sein
        // Eine Erweiterung der Funktion bietet der Parameter $ZERO_ROWS_IS_OK, mit dem man ein "leeres" Ergebnis eben doch als "ok" akzeptieren kann
    }

    // Wendet die Methode "trim" rekrusiv auf alle Werte in einem Array an
    // Warum hier? => siehe wasGetSuccessful ( .. )
    private function arraytrim ( &$array ) { // & bedeutet Call by Refernece anstatt dem normalen Call by Value
        foreach ( $array as $key => $value ) { // Array durchgehen
            if ( is_array($array[$key] ) ) {  // Wenn der Wert ein Array ist...
                $this->arraytrim ( $array[$key] ); // ... die Funktion rekusriv aufrufen
            } else {
                $array[$key] = trim ( $value ); // sonst "trim" anwenden
            }
        }
    }
    
    // Der Destruktor beendet zur Sicherheit die DB-Verbindung
    public function __destruct (){
        mysql_close ( $this->sql );
    }
}
?>