<?php
// Klasse zur Verwendung von einfachen Template-Dateien statt HTML-Code im PHP

// Quelle:
// Basiert auf Code aus dem Projekt "Frogsystem 2 [http://www.frogsystem.de/]"
// Ursprünglicher Hauptautor: Moritz "Sweil" Kornher, Co-Autor: "Satans Krümelmonster" (Pseudonym)
// für das Kino-Projekt teilweise überarbeitet und angepasst von Moritz Kornher

class Template
{
    // Definition der Opener/Closer-Konstanten
    const OPENER                = "{..";
    const CLOSER                = "..}";

    // Klassen-Variablen
    private $file               = null; // Die TPL-Datei
    private $tags               = array(); // Array der Template-Tags
    private $sections           = array(); // Array der Sections als Cache damit nicht immer die Datei eingelesen wird
    private $sections_content   = array(); // Array der Inhalte der Sections damit nicht immer die Datei eingelesen wird
    private $template           = null;

    // Der Konstruktur
    public function  __construct( $file ) {
        $this->setFile( $file );
    }

    // Getter für Opener/Closer
    public function getOpener () {
        return self::OPENER;
    }
    public function getCloser () {
        return self::CLOSER;
    }
    
    // Setzte Datei
    private function setFile ( $file ) {
        if ( file_exists ( ROOT_PATH."style/".$file ) ) { // Nur wenn die Datei auch existiert
            $this->file = $file;
            $this->clearSectionCache (); // Den SectionCache vorsichtshalber mal löschen
        } else {
            $this->__destruct (); // Sonst soll sich das Objekt selbst zerstören
        }
    }
    // Getter für Dateinamen
    private function getFile () {
        return $this->file;
    }
    
    // Setter & Getter für Sections, Section-Nummer, Section-Content
    private function setSections ( $sections ) {
        $this->sections = $sections;
    }
    private function getSectionNumber ( $section ) {
        return $this->sections[$section];
    }
    private function setSectionsContent ( $content ) {
        $this->sections_content = $content;
    }
    private function getSectionContent ( $section_number ) {
        return $this->sections_content[$section_number];
    }
    // Existiert eine Section?
    private function sectionExists ( $section ) {
        if ( isset ( $this->sections[$section] ) ) { // Wird im entsprechenden Array geprüft
            return TRUE;
        }
        return FALSE;
    }
    
    // Setzt den Section Cache zurück
    public function clearSectionCache() {
        unset ($this->sections);
        unset ($this->sections_content);
        $this->sections = array();
        $this->sections_content = array();
    }


    // Setzt die Tag-Liste zurück
    public function clearTags () {
        unset ($this->tags);
        $this->tags = array();
    }
    
    // Löscht einen einzelnen Tag
    public function deleteTag ( $tag ) {
        $this->tags[$tag] = null;
    }

    // Setter & Getter für das Ausgabe Template
    private function setTemplate($template) {
        $this->template = $template;
    }
    private function getTemplate() {
        return $this->template;
    }
    
    // Lädt eine alle Sections in den Section-Cache
    // und läd die angegebene Section zur Bearbeitung in $template
    public function load ( $section ) {
        // Wenn der Section-Cache wurde noch nicht befüllt wurde => alle Sections in den Cache laden
        if ( count ( $this->sections ) <= 0 ) {
            $file_path = ROOT_PATH . "style/" . $this->getFile (); // Pfad zur Template-Datei
            $FILE = new Fileaccess ( $file_path ); // Object für Dateizugriff erzeugen
            $search_expression = '/<!--section-start::(.*)-->(.*)<!--section-end::(\1)-->/Uis'; // Regulärer Ausruck um Sections auszuwählen
            $number_of_sections = preg_match_all ( $search_expression, $FILE->getData (), $sections ); // Regulären Ausruck ausführen, Anzahl in $number_of_sections, Inhalte in $sections
            $this->setSections ( array_flip ( $sections[1] ) ); // array_flip damit die Keys auch die Section-Namen sind
            $this->setSectionsContent ( $sections[2] ); // Section Inhalte speichern
        }
        
        // Section-Cache wurde bereits befüllt => einfach auslesen
        if ( $this->sectionExists ( $section ) ) {
            $this->setTemplate ( $this->getSectionContent ( $this->getSectionNumber ( $section ) ) );
            return TRUE;
        } else { // Falls die Section nicht gefunden wurde
            return FALSE;
        }
    }
    
    // toString-Methode um das Template als String auszugeben
    public function __toString() {
        $data = $this->getTemplate (); // aktuelles Template laden
        foreach ( $this->tags as $theTag => $value ) { // Tag-Liste durchgehen
            if ( $value !== null ) {
                $data = str_replace ( self::OPENER . $theTag . self::CLOSER, $value, $data ); // Tags durch Werte ersetzen
            }
        }
        return $data;
    }

    // Setzt Template-Tag mit zugehöriger Ersetzung
    public function tag ( $tag, $value ) {
        $this->tags[$tag] = $value;
    }
    
    // Destruktor
    public function  __destruct(){
        $this->clearSectionCache();
        $this->clearTags();
    }
}
?>