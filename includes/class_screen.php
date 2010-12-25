<?php
// Diese Datei stellt einige Klassen für einen Kino-Saal bereit

// Interface für Kino-Saal Elemente
interface ScreenRoomElement
{
    // kann gefragt werden ob es ein Sitz ist
    public function isSeat();
}


// Klasse die einen Leinwand-Element darstellt darstellt
class ScreenElement implements ScreenRoomElement
{
    // Klassen-Variablen
    // Hat keine weiteren Eigenschaften

    // Der Konstruktur
    public function  __construct() {
    }
    
    // toString-Methode der das Element darstellt
    public function __toString() {
        $theTemplate = new Template ( "screen.tpl" );
        $theTemplate->load ( "SCREEN_ELEMENT" );
        return (string) $theTemplate;
    }
    
    // kein Sitz
    public function isSeat () {
        return FALSE;
    }
}

// Klasse die einen Space-Element darstellt darstellt
class Space implements ScreenRoomElement
{
    // Klassen-Variablen
    // Hat keine weiteren Eigenschaften

    // Der Konstruktur
    public function  __construct() {
    }

    // toString-Methode der das Element darstellt
    public function __toString() {
        $theTemplate = new Template ( "screen.tpl" );
        $theTemplate->load ( "SPACE" );
        return (string) $theTemplate;
    }

    // kein Sitz
    public function isSeat () {
        return FALSE;
    }
}

// Klasse die einen Sitz-Element darstellt
class Seat implements ScreenRoomElement
{
    // Klassen-Variablen
    protected $number; // Platznummer
    protected $state = 0; // Status des Platzes: 0 = frei; 1 = reserviert; 2 = verkauft; 3 = geblockt (temporär reserviert)

    // Der Konstruktur
    public function  __construct( $number ) {
        $this->number = $number;
    }
    
    // Getter für $number
    public function getNumber () {
        return $this->number;
    }
    
    // Getter/Setter für $state
    public function setState ( $state ) {
        if ( in_array ( $state, array ( 0, 1, 2, 3 ) ) ) {
            $this->state = $state;
        }
    }
    public function getState () {
        return $this->state;
    }

    // ist ein Sitz
    public function isSeat () {
        return TRUE;
    }

    // toString-Methode der das Element darstellt
    public function __toString() {
        $theTemplate = new Template ( "screen.tpl" );
        switch ( $this->state ) {
            case 3: // Blockiert / ausgewählt
                $theTemplate->load ( "SEAT_BLOCKED" );
                break;
            case 2: // Verkauft
                $theTemplate->load ( "SEAT_SOLD" );
                break;
            case 1: // reserviert
                $theTemplate->load ( "SEAT_RESERVATION" );
                break;
            default: // frei
                $theTemplate->load ( "FREE" );
                break;
        }
        $theTemplate->tag ( "number", $this->getNumber() );
        return (string) $theTemplate;
    }
}

// Klasse die einen Rollstuhlplatz darstellt
class WheelChairSeat extends Seat implements ScreenRoomElement
{
    // Erbt alles von der Eltern-Klasse bis auf die __toString()

    // toString-Methode der das Element darstellt
    public function __toString() {
        if ( $this->state == 0 ) { // Wenn der PLatz frei ist ...
            $theTemplate = new Template ( "screen.tpl" );
            $theTemplate->load ( "SEAT_WHEELCHAIR" ); // ... wird ein anderes Template geladen ...
            $theTemplate->tag ( "number", $this->getNumber() );
            return (string) $theTemplate;
        } else {
            return parent::__toString(); // ...ansonsten ruft sie die __toString der Eltern-Klasse auf
        }
    }
}


// Klasse die einen ganzen Kino-Saal darstellt
class ScreenRoom
{
    // Klassen-Variablen
    private $theSql;                       // Die Datenbank-Verbindung
    private $screen_data        = array(); // Screeninfo

    // Der Konstruktur
    public function  __construct( $screen_data_text ) {
        // Datenbank-Verbindung erlauben ...
        global $sql;
        $this->theSql = $sql; // ... und der Klassen-Variable zuweisen
        $this->setScreenData ( $screen_data_text );
    }

    // Setter für ScreenData
    public function setScreenData ( $screen_data_text ) {
        $lines = explode ( "\n", $screen_data_text ); // Am Zeilenumbruch in Array aufspalten
        $seat_number = 1; // Sitze ab 1 durchnummerieren
        foreach ( $lines as $lineKey => $aLine ) { // Jedes Zeile durchgehen
            $seats = str_split ( $aLine ); // Die Zeile in einzelne Zeichen splitten
            $seat_array = array (); // neues Array, damit evtl. nicht zugehörige Zeichen übergangen werden
            foreach ( $seats as $aSeat ) { // Jedes Zeichen durchgehen
                switch ( $aSeat ) { // Das akuelle Zeichen switchen und da entsprechende Objekt speichern
                    case "x":
                        $seat_array[] = new Seat ( $seat_number );
                        $seat_number++; // Nur bei Sitzen die Platznummer erhöhen
                        break;
                    case "o":
                        $seat_array[] = new Space ();
                        break;
                    case "-":
                        $seat_array[] = new ScreenElement ();
                        break;
                    case "h":
                        $seat_array[] = new WheelChairSeat ( $seat_number );
                        $seat_number++;  // Nur bei Sitzen die Platznummer erhöhen
                        break;
                }
            }
            $lines[$lineKey] = $seat_array;  // Zeichen/Element-Array in die Zeile einfügen
        }

        $this->screen_data = $lines; // Und das ganze ablegen
    }
    
    // Staus für die einzelnen Sitze setzen, abhängig von Show und User
    public function setStates ( $show_id, $user_id ) {
        $ticket_data = getTicketData ( $show_id, $user_id ); // Die Ticket Daten einlesen
        
        // Jedes Elemnt durchgehen
        foreach ( $this->screen_data as $aLine ) {
            foreach ( $aLine as $aSeat ) {
                if ( $aSeat->isSeat () ) { // Wenn wir auf einen Sitz treffen
                    $theSeatNumber = $aSeat->getNumber ();
                    if ( in_array ( $theSeatNumber, $ticket_data[0] ) ) { // nachschauen, ob er reserviet ist
                        $aSeat->setState ( 1 );
                    } elseif ( in_array ( $theSeatNumber, $ticket_data[1] ) ) { // nachschauen, ob er verkauft ist
                        $aSeat->setState ( 2 );
                    } elseif ( in_array ( $theSeatNumber, $ticket_data[2] ) ) { // nachschauen, ob er blockiert ist
                        $aSeat->setState ( 3 );
                    }
                }
            }
        }

    }
    
    // toString-Methode um den Saal als String auszugeben
    public function __toString() {
        // Template laden
        $theTemplate = new Template ( "screen.tpl" );

        // leeren String initialisieren
        $string_screen_room = "";

        // Die Zeilen und Zeichen/Elemente durchgehen
        foreach ( $this->screen_data as $aLine ) {
            // Zeilen-Template laden
            $theTemplate->load ( "SCREEN_LINE_CONTAINER" );
            $theTemplate->clearTags ();
            $string_line = "";
            // Die Zeichen/Elemente durchgehen
            foreach ( $aLine as $aSeat ) {
                $string_line .= (string) $aSeat; // jeweils einfach die String-Darstellung des Elements laden
            }
            $theTemplate->tag ( "seats", $string_line ); // ins Zeilen Template einfügen
            $string_screen_room .= (string) $theTemplate;
        }
        
        // Container-Template für einen Vorführaum laden
        $theTemplate->load ( "SCREEN_CONTAINER" );
        $theTemplate->clearTags ();
        $theTemplate->tag ( "screen_room", $string_screen_room ); // Die tatsächlichen Raum-Elemente einfügen

        // alles ausgeben
        return (string) $theTemplate;
    }
}
?>