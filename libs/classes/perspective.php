<?php
/**
 * Diese Klasse repraesentiert eine Perspektive einer Balanced Scorecard (BSC) mitsamt ihrer Indikatoren.
 *
 * Sie stellt Methoden zur Abfrage und Aenderung ihrer MetaDaten (Name, Beschreibung) zur Verfuegung.
 * Die Gewichtung sowie Berechnung der Effizienz und des Fortschritts beziehen sich auf das angegebene Datum.
 * Ebenso die zugeordneten Indikatoren.
 * Als Grundvorausetzung wird eine MySQLi Datenbankverbindung erwartet.
 *
 * @author Stefan Wilke
 * @package BSC.Essentials
 */
class Perspective
{
    private $db = null;
    private $bsc = null;
    private $per = null;
    private $name = 'Neue Perspektive';
    private $description = null;
    private $date = null;
    private $weight = 1;
    private $indicators = array();

    /**
     * Konstruktur zum Laden oder Erstellen einer Perspektive.
     *
     * Fuer die Instanziierung wird entweder eine gueltige ID der Perspektive oder der uebergeordneten BSC benoetigt. In Abhaengigkeit davon wird eine vorhandene Perspektive geladen oder eine neue erstellt.
     * Falls kein Datum angegeben wurde, wird das heutige Datum verwendet.
     * Eine neue Perspektive hat standardmaessig die Bezeichnung 'Neue Perspektive' und ein Gewichtung von 1.
     *
     * @param MySQLi $db MySQLi Datenbankverbindung
     * @param Integer $per ID der Perspektive
     * @param Integer $bsc ID der uebergeordneten Balanced Scorecard
     * @param Date|null $date angefordertes Datum
     */
    public function __construct($db = null, $per = null, $bsc = null, $date = null)
    {
        if (!isSet($db))
            throw new Exception('No valid database connection!');

        if (!isSet($per) && !isSet($bsc))
            throw new Exception('Either one perspective identifier or one scorecard identifier must be given!');

        // if no date is specified today's date will be used instead
        if (!isSet($date))
            $date = date('d.m.Y');

        $this->db = $db;
        $this->date = $date;

        // create a new perspective
        if (!isSet($per) && isSet($bsc))
        {
            $result = $this->db->prepare('SELECT * FROM scorecard WHERE bsc_id = ?');
            $result->bind_param('i', $bsc);
            $result->execute();
            $result->store_result();

            // BSC exists?
            if ($result->num_rows != 1)
                throw new Exception('Unknown scorecard!');

            $result->free_result();

            $this->SetScorecard($bsc);
        }
        // load existing perspective
        else if (isSet($per))
        {
            $result = $this->db->prepare('SELECT per_name, per_description, bsc_id FROM perspective WHERE per_id = ?');
            $result->bind_param('i', $per);
            $result->execute();
            $result->store_result();

            // Perspective exists?
            if ($result->num_rows != 1)
                throw new Exception('Unknown perspective!');

            $result->bind_result($per_name, $per_description, $bsc_id);
            $result->fetch();
            $result->free_result();

            $this->SetScorecard($bsc_id);
            $this->SetIdentifier($per);
            $this->SetName($per_name);
            $this->SetDescription($per_description);

            $this->ReceiveWeight();
            $this->ReceiveIndicators();
        }
    }

    /**
     * Stellt sicher, dass das angegebene Datum in der Datenbank (Tabelle: calendar) vorhanden ist.
     * Falls nicht, wird es hinzugefuegt.
     */
    private function EnsureDate()
    {
        $result = $this->db->prepare('SELECT * FROM calendar WHERE cal_day = ?');
        $result->bind_param('s', $this->GetDate());
        $result->execute();
        $result->store_result();

        if ($result->num_rows < 1)
        {
            $result = $this->db->prepare('INSERT INTO calendar VALUES (?)');
            $result->bind_param('s', $this->GetDate());
            $result->execute();
        }
    }

    /**
     * Speichert die Perspektive und ihre aktuellen Daten in der Datenbank.
     */
    public function Save()
    {
        $per = $this->GetIdentifier();

        // update existing perspective
        if ( isSet($per) )
        {
            $result = $this->db->prepare('SELECT * FROM perspective_metrics WHERE per_id = ? AND cal_day = ?');
            $result->bind_param('is', $this->GetIdentifier(), $this->GetDate());
            $result->execute();
            $result->store_result();

            if ($result->num_rows == 0)
            {
                $result = $this->db->prepare('INSERT INTO perspective_metrics VALUES (?, ?, ?)');
                $result->bind_param('isi', $this->GetIdentifier(), $this->GetDate(), $this->GetWeight());
            }
            else
            {
                $result = $this->db->prepare('UPDATE perspective_metrics SET per_weight = ? WHERE per_id = ? AND cal_day = ?');
                $result->bind_param('iis', $this->GetWeight(), $this->GetIdentifier(), $this->GetDate());
            }

            $result->execute();
            $result = $this->db->prepare('UPDATE perspective SET per_name = ?, per_description = ? WHERE per_id = ?');
            $result->bind_param('ssi', $this->GetName(), $this->GetDescription(), $this->GetIdentifier());
            $result->execute();
        }
        // insert new perspective
        else
        {
            $result = $this->db->prepare('INSERT INTO perspective VALUES (NULL, ?, ?, ?)');
            $result->bind_param('iss', $this->GetScorecard(), $this->GetName(), $this->GetDescription());
            $result->execute();
            $result->store_result();

            $this->SetIdentifier( $this->db->insert_id );

            $this->EnsureDate();
            $result = $this->db->prepare('INSERT INTO perspective_metrics VALUES (?, ?, ?)');
            $result->bind_param('isi', $this->GetIdentifier(), $this->GetDate(), $this->GetWeight());
            $result->execute();
        }
    }

    /**
     * Entfernt die Perspektive, ihre Daten und alle zugeordneten Indikatoren.
     */
    public function Delete()
    {
        foreach($this->indicators as $indicator)
            $indicator->Delete();

        $result = $this->db->prepare('DELETE FROM perspective_metrics WHERE per_id = ?');
        $result->bind_param('i', $this->GetIdentifier());
        $result->execute();
        $result = $this->db->prepare('DELETE FROM perspective WHERE per_id = ?');
        $result->bind_param('i', $this->GetIdentifier());
        $result->execute();

        // set identifier to null
        $this->SetIdentifier();
    }

    /**
     * Laedt die Gewichtung der Perspektive zum angegebenen Datum.
     */
    private function ReceiveWeight()
    {
        $result = $this->db->prepare('SELECT pm.per_weight FROM calendar c LEFT JOIN perspective_metrics pm USING (cal_day) WHERE pm.per_id = ? AND TO_DAYS(STR_TO_DATE(?, GET_FORMAT(DATE, "EUR"))) - TO_DAYS(STR_TO_DATE(c.cal_day, GET_FORMAT(DATE, "EUR"))) >= 0 ORDER BY STR_TO_DATE(c.cal_day, GET_FORMAT(DATE, "EUR")) DESC LIMIT 1');
        $result->bind_param('is', $this->GetIdentifier(), $this->GetDate());
        $result->execute();
        $result->store_result();

        if ($result->num_rows > 0)
        {
            $result->bind_result($per_weight);
            $result->fetch();
            $result->free_result();

            $this->SetWeight($per_weight);
        }
    }

    /**
     * Laedt alle zugeordneten Indikatoren der Perspektive zum angegebenen Datum.
     */
    private function ReceiveIndicators()
    {
        $result = $this->db->prepare('SELECT ind_id FROM indicator WHERE per_id = ?');
        $result->bind_param('i', $this->GetIdentifier());
        $result->execute();
        $result->store_result();
        $result->bind_result($indicator);

        while ($result->fetch())
            $this->indicators[] = new Indicator($this->db, $indicator, null, $this->GetDate());

        $result->free_result();
    }

    /**
     * Liefert saemtliche Indikatoren, die zum Zeitpunkt der Instanziierung vorhanden waren.
     * @return Indicator[] Liste der Indikatoren
     */
    public function GetIndicators()
    {
        return $this->indicators;
    }

    /**
     * Setzt die ID der Perspektive.
     * @param Integer|null ID
     */
    private function SetIdentifier($per = null)
    {
        if (!isSet($per) || (is_numeric($per) && $per > 0))
            $this->per = $per;
    }

    /**
     * Liefert die ID der Perspektive.
     * @return Integer|null ID
     */
    public function GetIdentifier()
    {
        return $this->per;
    }

    /**
     * Liefert die ID der uebergeordneten Balanced Scorecard.
     * @return Integer ID
     */
    public function GetScorecard()
    {
        return $this->bsc;
    }

    /**
     * Teilt der Perspektive ihre uebergeordnete Balanced Scorecard zu.
     * Die angegebene BSC wird dabei nicht auf ihre Existenz ueberprueft und darf nicht null sein.
     * @param Integer ID
     */
    private function SetScorecard($bsc = null)
    {
        if ( isSet($bsc) )
            $this->bsc = $bsc;
    }

    /**
     * Setzt den Namen der Perspektive, falls dieser min. 1 Zeichen enthaelt.
     * @param String Name
     */
    public function SetName($name = null)
    {
        if (isSet($name) && strLen($name) > 0)
            $this->name = $name;
    }

    /**
     * Liefert den aktuellen Namen der Perspektive.
     * @return String Name
     */
    public function GetName()
    {
        return $this->name;
    }

    /**
     * Setzt die Beschreibung der Perspektive (optional).
     * @param String|null Beschreibung
     */
    public function SetDescription($description = null)
    {
        $this->description = $description;
    }

    /**
     * Liefert die Beschreibung der Perspektive, falls vorhanden.
     * @return String|null Beschreibung
     */
    public function GetDescription()
    {
        return $this->description;
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
     * Setzt die Gewichtung der Perspektive im Bezug auf das angegebene Datum.
     * Die Gewichtung muss min. 1 betragen.
     * @param Integer Gewichtung
     */
    public function SetWeight($weight = null)
    {
        if (is_numeric($weight) && $weight > 0)
            $this->weight = $weight;
    }

    /**
     * Liefert die Gewichtung der Perspektive im Bezug auf das angegebene Datum.
     * @return Integer Gewichtung
     */
    public function GetWeight()
    {
        return $this->weight;
    }

    /**
     * Berechnet die Effizienz der Perspektive zum angegebenen Datum unter Beruecksichtigung ihrer Indikatoren (deren Werte und Gewichtungen).
     * @return Integer Effizienz
     */
    public function GetPerformance()
    {
        foreach ($this->indicators as $indicator)
        {
        	$efficiency += $indicator->GetPerformance() * $indicator->GetWeight();
            $weight += $indicator->GetWeight();
        }

        if ($weight == 0)
        	$weight = 1;

        return $efficiency / $weight;
    }

    /**
     * Berechnet den Fortschritt der Perspektive zum angegebenen Datum unter Beruecksichtigung ihrer Indikatoren (deren Werte und Gewichtungen).
     * @return Integer Fortschritt
     */
    public function GetProgress()
    {
        foreach ($this->indicators as $indicator)
        {
        	$progress += $indicator->GetProgress() * $indicator->GetWeight();
            $weight += $indicator->GetWeight();
        }

        if ($weight == 0)
        	$weight = 1;

        return $progress / $weight;
    }
}

?>
