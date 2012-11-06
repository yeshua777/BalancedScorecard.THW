<?php
/**
 * Diese Klasse repraesentiert eine Balanced Scorecard (BSC) mitsamt ihrer Perspektiven.
 *
 * Sie stellt Methoden zur Abfrage und Aenderung ihrer MetaDaten (Name, Beschreibung) zur Verfuegung.
 * Die Berechnung der Effizienz und des Fortschritts beziehen sich auf das angegebene Datum.
 * Ebenso die zugeordneten Perspektiven.
 * Als Grundvorausetzung wird eine MySQLi Datenbankverbindung erwartet.
 *
 * @author Stefan Wilke, Eric Schulz
 * @package BSC.Essentials
 */
class Scorecard
{
    private $db = null;
    private $bsc = null;
    private $name = 'Neue Balanced Scorecard';
    private $description = null;
    private $date = null;
    private $beginning = null;
    private $perspectives = array();

    /**
     * Konstruktur zum Laden oder Erstellen einer Balanced Scorecard (BSC).
     *
     * Bei Angabe einer gueltigen ID bei der Instanziierung wird die BSC geladen, ansonsten wird eine neue erstellt.
     * Falls kein Datum angegeben wurde, wird das heutige Datum verwendet.
     * Eine neue BSC hat standardmaessig die Bezeichnung 'Neue Balanced Scorecard'.
     *
     * @param MySQLi $db MySQLi Datenbankverbindung
     * @param Integer $bsc ID der Balanced Scorecard
     * @param Date|null $date angefordertes Datum
     */
    public function __construct($db = null, $bsc = null, $date = null)
    {
        if (!isSet($db))
            throw new Exception('No valid database connection!');

        // if no date is specified today's date will be used instead
        if (!isSet($date))
            $date = date('d.m.Y');

        $this->db = $db;
        $this->date = $date;
        $this->beginning = $date;

        // load existing scorecard
        if (isSet($bsc))
        {
            $result = $this->db->prepare('SELECT bsc_name, bsc_description, bsc_dateCreated FROM scorecard WHERE bsc_id = ?');
            $result->bind_param('i', $bsc);
            $result->execute();
            $result->store_result();

            // BSC exists?
            if ($result->num_rows != 1)
                throw new Exception('Unknown scorecard!');

            $result->bind_result($bsc_name, $bsc_description, $bsc_beginning);
            $result->fetch();
            $result->free_result();

            $this->SetIdentifier($bsc);
            $this->SetName($bsc_name);
            $this->SetDescription($bsc_description);
            $this->SetBeginning($bsc_beginning);
            $this->ReceivePerspectives();
        }
    }

    /**
     * Laedt alle zugeordneten Perspektiven der Balanced Scorecard zum angegebenen Datum.
     */
    private function ReceivePerspectives()
    {
        $result = $this->db->prepare('SELECT per_id FROM perspective WHERE bsc_id = ?');
        $result->bind_param('i', $this->GetIdentifier());
        $result->execute();
        $result->store_result();
        $result->bind_result($perspective);

        while ($result->fetch())
            $this->perspectives[] = new Perspective($this->db, $perspective, null, $this->GetDate());

        $result->free_result();
    }

    /**
     * Speichert die Balanced Scorecard und ihre aktuellen Daten in der Datenbank.
     */
    public function Save()
    {
        $bsc = $this->GetIdentifier();

        // update existing scorecard
        if ( isSet($bsc) )
        {
            $result = $this->db->prepare('UPDATE scorecard SET bsc_name = ?, bsc_description = ? WHERE bsc_id = ?');
            $result->bind_param('ssi', $this->GetName(), $this->GetDescription(), $this->GetIdentifier());
            $result->execute();
        }
        // insert new scorecard
        else
        {
            $result = $this->db->prepare('INSERT INTO scorecard VALUES (NULL, ?, ?, ?)');
            $result->bind_param('sss', $this->GetName(), $this->GetDescription(), $this->GetBeginning());
            $result->execute();
            $result->store_result();

            $this->SetIdentifier( $this->db->insert_id );

            $result->free_result();
        }
    }

    /**
     * Entfernt die BSC, ihre Daten und alle zugeordneten Perspektiven.
     */
    public function Delete()
    {
        foreach($this->perspectives as $perspective)
            $perspective->Delete();

        $result = $this->db->prepare('DELETE FROM scorecard WHERE bsc_id = ?');
        $result->bind_param('i', $this->GetIdentifier());
        $result->execute();

        // set identifier to null
        $this->SetIdentifier();
    }

    /**
     * Liefert eine Liste saemtlicher Scorecards (ID und Name).
     * @param MySQLi $db MySQLi Datenbankverbindung
     * @return Scorecard[] Liste der BSCs
     */
    public static function GetScorecards($db = null)
    {
        if (!isSet($db))
            throw new Exception('No valid database connection!');

        $scorecards = array();
        $result = $db->query('SELECT bsc_id, bsc_name FROM scorecard');

        while ($row = $result->fetch_assoc())
            $scorecards[] = $row;

        $result->free_result();

        return $scorecards;
    }

    /**
     * Liefert die ID der BSC.
     * @return Integer ID
     */
    public function GetIdentifier()
    {
        return $this->bsc;
    }

    /**
     * Setzt die ID der BSC.
     * @param Integer|null ID
     */
    private function SetIdentifier($bsc = null)
    {
        if (!isSet($bsc) || (is_numeric($bsc) && $bsc > 0))
            $this->bsc = $bsc;
    }

    /**
     * Liefert das BezugsDatum, das bei der Instanziierung angegeben wurde.
     * @return Date Datum
     */
    public function GetDate()
    {
        return $this->date;
    }

    /**
     * Liefert saemtliche Perspektiven, die zum Zeitpunkt der Instanziierung vorhanden waren.
     * @return Perspective[] Liste der Perspektiven
     */
    public function GetPerspectives()
    {
        return $this->perspectives;
    }

    /**
     * Liefert den aktuellen Namen der BSC.
     * @return String Name
     */
    public function GetName()
    {
        return $this->name;
    }

    /**
     * Setzt den Namen der BSC, falls dieser min. 1 Zeichen enthaelt.
     * @param String Name
     */
    public function SetName($name = null)
    {
        if (isSet($name) && strLen($name) > 0)
            $this->name = $name;
    }

    /**
     * Liefert die Beschreibung der BSC, falls vorhanden.
     * @return String|null Beschreibung
     */
    public function GetDescription()
    {
        return $this->description;
    }

    /**
     * Setzt die Beschreibung der BSC (optional).
     * @param String|null Beschreibung
     */
    public function SetDescription($description = null)
    {
        $this->description = $description;
    }

    /**
     * Liefert das Erstellungsdatum der BSC.
     * @return Date Erstellungsdatum
     */
    public function GetBeginning()
    {
        return $this->beginning;
    }

    /**
     * Setzt das Erstellungsdatum der BSC.
     * @param Date $beginning Erstellungsdatum
     */
    private function SetBeginning($beginning = null)
    {
        $this->beginning = $beginning;
    }

    /**
     * Berechnet die Effizienz der BSC zum angegebenen Datum unter Beruecksichtigung ihrer Perspektiven (deren Werte und Gewichtungen).
     * @return Integer Effizienz
     */
    public function GetPerformance()
    {
        foreach ($this->perspectives as $perspective)
        {
        	$efficiency += $perspective->GetPerformance() * $perspective->GetWeight();
            $weight += $perspective->GetWeight();
        }

        if ($weight == 0)
        	$weight = 1;

        return $efficiency / $weight;
    }

    /**
     * Berechnet den Fortschritt der BSC zum angegebenen Datum unter Beruecksichtigung ihrer Perspektiven (deren Werte und Gewichtungen).
     * @return Integer Fortschritt
     */
    public function GetProgress()
    {
        foreach ($this->perspectives as $perspective)
        {
        	$progress += $perspective->GetProgress() * $perspective->GetWeight();
            $weight += $perspective->GetWeight();
        }

        if ($weight == 0)
        	$weight = 1;

        return $progress / $weight;
    }
}

?>