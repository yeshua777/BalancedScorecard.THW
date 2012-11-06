<?php

/**
 * Diese Klasse repraesentiert einen Indikator einer Balanced Scorecard (BSC).
 *
 * Sie stellt Methoden zur Abfrage und Aenderung seiner MetaDaten (Name, Beschreibung, Einheit), seines Wertes und seiner Metrik (Startwert, Zielwert, Minimum, Maximium, Gewichtung, Maximierung) zur Verfuegung.
 * Der Wert und die Metrik, sowie Berechnung der Effizienz und des Fortschritts, beziehen sich auf das angegebene Datum.
 * Als Grundvorausetzung wird eine MySQLi Datenbankverbindung erwartet.
 *
 * @author Patrick Dierking
 * @package BSC.Essentials
 */
class indicator {

    private $ind_id = null;
    private $per_id = null;
    private $name = "Neuer Indikator";
    private $description = null;
    private $unit = null;
    private $value = null;
    private $date = null;
    private $min = null;
    private $max = null;
    private $target = null;
    private $weight = 1;
    private $base = null;
    private $maximize = true;
    private $db = null;

    /**
     * Konstruktur zum Laden oder Erstellen eines Indikators.
     *
     * Fuer die Instanziierung wird entweder eine gueltige ID des Indikators oder der uebergeordneten Perspektive benoetigt. In Abhaengigkeit davon wird ein vorhandener Indikator geladen oder ein neuer erstellt.
     * Falls kein Datum angegeben wurde, wird das heutige Datum verwendet.
     * Ein neuer Indikator hat standardmaessig die Bezeichnung 'Neuer Indikator' und eine Gewichtung von 1.
     *
     * @param MySQLi $db MySQLi Datenbankverbindung
     * @param Integer|null $ind_id ID des Indikators
     * @param Integer|null $per_id ID der Perspektive
     * @param Date|null $date angefordertes Datum
     */
    public function __construct($db = null, $ind_id = null, $per_id = null, $date = null) {
        if (isSet($db)) {
            $this->db = $db;

            // if no date is specified today's date will be used instead
            if (isSet($date)) {
                $this->date = $date;
            } else {
                $this->date = date('d.m.Y');
            }

            if (isset($ind_id)) {

                //indicator: Name, Beschreibung, Einheit und die ID der Perspektive aus der DB holen
                $sql = "SELECT i.ind_name, i.ind_description, i.ind_unit, i.per_id
						FROM indicator i
						WHERE i.ind_id = ?";
                $result = $this->db->prepare($sql) or die($db->error);
                $result->bind_param('i', $ind_id);
                $result->execute();
                $result->bind_result($name, $description, $unit, $per_id);
                $result->fetch();

                $this->ind_id = $ind_id;
                $this->per_id = $per_id;
                $this->name = $name;
                $this->unit = $unit;
                $this->description = $description;
                $result->free_result();

                //value: Wert aus der DB holen
                $sql = "SELECT iv.ind_value
						FROM indicator_value iv
						INNER JOIN calendar c USING(cal_day)
						WHERE iv.ind_id = ? AND TO_DAYS(STR_TO_DATE(?, GET_FORMAT(DATE, 'EUR'))) - TO_DAYS(STR_TO_DATE(c.cal_day, GET_FORMAT(DATE, 'EUR'))) >= 0 ORDER BY STR_TO_DATE(c.cal_day, GET_FORMAT(DATE, 'EUR')) DESC LIMIT 1";
                $result = $this->db->prepare($sql) or die($db->error);
                $result->bind_param('is', $ind_id, $this->date);
                $result->execute();
                $result->bind_result($value);
                $result->fetch();
                $this->value = $value;
                $result->free_result();

                //metric: Minimum, Maximum, Basis, Ziel, Gewichtung und ob Maximiert werden soll aus der DB holen
                $sql = "SELECT ind_min, ind_max, ind_base, ind_target, ind_weight, ind_maximize
						FROM indicator_metric
						INNER JOIN calendar c USING(cal_day)
						WHERE ind_id = ? AND TO_DAYS(STR_TO_DATE(?, GET_FORMAT(DATE, 'EUR'))) - TO_DAYS(STR_TO_DATE(c.cal_day, GET_FORMAT(DATE, 'EUR'))) >= 0 ORDER BY STR_TO_DATE(c.cal_day, GET_FORMAT(DATE, 'EUR')) DESC LIMIT 1";
                $result = $this->db->prepare($sql) or die($db->error);
                $result->bind_param('is', $ind_id, $this->date);
                $result->execute();
                $result->bind_result($min, $max, $base, $target, $weight, $maximize);
                $result->fetch();

                $this->min = $min;
                $this->max = $max;
                $this->target = $target;
                $this->weight = $weight;
                $this->base = $base;
                $this->maximize = $maximize;
                $result->free_result();
            } else if (isSet($per_id)) {
                $this->per_id = $per_id;
            } else {
                echo 'Either one indicator identifier or one perspective identifier must be given!';
                throw new Exception('Either one indicator identifier or one perspective identifier must be given!');
            }
        } else {
            echo 'No valid database connection!';
            throw new Exception('No valid database connection!');
        }
    }

    /**
     * Speichert den Indikator, die Metrik und den Wert in der Datenbank.
     */
    public function Save() {

        //Ueberpruefung ob die Perspektive vorhanden ist
        $result = $this->db->prepare('SELECT * FROM perspective WHERE per_id = ?');
        $result->bind_param('i', $this->per_id);
        $result->execute();
        $result->store_result();

        if ($result->num_rows == 1) {
            //Ueberpruefung ob der Indikator schon vorhanden ist
            if (isSet($this->ind_id)) {

                //Aktuallisieren des Indicators
                $result->free_result();
                $sql = "UPDATE indicator SET ind_name = ?, ind_description = ?, ind_unit = ? WHERE ind_id = ?";
                $result = $this->db->prepare($sql);
                $result->bind_param('sssi', $this->name, $this->description, $this->unit, $this->ind_id);
                $result->execute();
            } else {

                //Neuen Indikator einfuegen
                $result->free_result();
                $sql = "INSERT INTO indicator VALUES ('', ?, ?, ?, ?)";
                $result = $this->db->prepare($sql);
                $result->bind_param('isss', $this->per_id, $this->name, $this->description, $this->unit);
                $result->execute();
                $this->ind_id = $result->insert_id;
            }

            $this->EnsureDate();

            //Ueberpruefung ob der Startwert, das Ziel oder der Wert in die Skala zwiscehn Minimum und Maximum passt.
            if ($this->base >= $this->min && $this->base <= $this->max && $this->target >= $this->min && $this->target <= $this->max && $this->value >= $this->min && $this->value <= $this->max) {

                //Ueberpruefung ob der Wert am angegebenen Datum schon vorhanden ist
                $sql = "SELECT * FROM indicator_value WHERE ind_id = ? AND cal_day = ?";
                $result = $this->db->prepare($sql);
                $result->bind_param('is', $this->ind_id, $this->date);
                $result->execute();
                $result->store_result();

                if ($result->num_rows == 1) {

                    //Aktualisierung des Werts
                    $result->free_result();
                    $sql = "UPDATE indicator_value SET ind_value = ? WHERE ind_id = ? AND cal_day = ?";
                    $result = $this->db->prepare($sql);
                    $result->bind_param('sis', $this->value, $this->ind_id, $this->date);
                    $result->execute();
                } else if ($this->value != null) {

                    //Neuen Wert einfuegen
                    $result->free_result();
                    $sql = "INSERT INTO indicator_value VALUES (?, ?, ?)";
                    $result = $this->db->prepare($sql);
                    $result->bind_param('iss', $this->ind_id, $this->date, $this->value);
                    $result->execute();
                }

                //Ueberpruefung ob die Metrik am angegebenen Datum schon vorhanden ist
                $sql = "SELECT * FROM indicator_metric WHERE ind_id = ? AND cal_day = ?";
                $result = $this->db->prepare($sql);
                $result->bind_param('is', $this->ind_id, $this->date);
                $result->execute();
                $result->store_result();

                if ($result->num_rows == 1) {

                    //Aktualisierung der Metrik
                    $result->free_result();
                    $sql = "UPDATE indicator_metric SET ind_min = ?, ind_max = ?, ind_base = ?, ind_target = ?, ind_weight = ?, ind_maximize = ? WHERE ind_id = ? AND cal_day = ?";
                    $result = $this->db->prepare($sql);
                    $result->bind_param('iiiiiiis', $this->min, $this->max, $this->base, $this->target, $this->weight, $this->maximize, $this->ind_id, $this->date);
                    $result->execute();
                } else if ($this->min != null and $this->max != null and $this->base != null and $this->target != null and $this->weight != null and $this->maximize != null) {

                    //Neue Metrik einfuegen
                    $result->free_result();
                    $sql = "INSERT INTO indicator_metric VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $result = $this->db->prepare($sql);
                    $result->bind_param('isiiiiii', $this->ind_id, $this->date, $this->min, $this->max, $this->base, $this->target, $this->weight, $this->maximize);
                    $result->execute();
                }
            } else {
                throw new Exception('Base, Target or Value is not between min and max!');
            }
        } else {
            throw new Exception('Unkwown perspective!');
        }
    }

    /**
     * Loescht den Indikator, seine Metrik und Werte aus der Datenbank.
     */
    public function Delete() {

        //Werte loeschen
        $sql = "DELETE FROM indicator_value WHERE ind_id = ?";
        $result = $this->db->prepare($sql);
        $result->bind_param('i', $this->ind_id);
        $result->execute();

        //Metrik loeschen
        $sql = "DELETE FROM indicator_metric WHERE ind_id = ?";
        $result = $this->db->prepare($sql);
        $result->bind_param('i', $this->ind_id);
        $result->execute();

        //Indicator loeschen
        $sql = "DELETE FROM indicator WHERE ind_id = ?";
        $result = $this->db->prepare($sql);
        $result->bind_param('i', $this->ind_id);
        $result->execute();
    }

    /**
     * Stellt sicher, dass das angegebene Datum in der Datenbank (Tabelle: calendar) vorhanden ist.
     * Falls nicht, wird es hinzugefuegt.
     */
    private function EnsureDate() {

        //Ueberpruefung ob das Datum in der DB exisitiert
        $sql = "SELECT * FROM calendar WHERE cal_day = ?";
        $result = $this->db->prepare($sql);
        $result->bind_param('s', $this->date);
        $result->execute();
        $result->store_result();

        if ($result->num_rows != 1) {

            //Datum einfuegen
            $sql = "INSERT INTO calendar VALUES (?)";
            $result = $this->db->prepare($sql);
            $result->bind_param('s', $this->date);
            $result->execute();
        }
        $result->free_result();
    }

    /**
     * Gibt die Effizenz des Indikators zurueck.
     * @return Integer|null Effizenz
     */
    public function GetPerformance() {
        //Ueberpruefung von min und max ob die Differenz 0 oder null ist
        if (($this->max - $this->min) != 0 and ($this->max - $this->min) != null) {
            //Ueberpruefung ob maximieren oder minimieren
            if ($this->maximize == 1) {
                return ($this->value - $this->min) / ($this->max - $this->min) * 100;
            } else {
                return ($this->max - $this->value) / ($this->max - $this->min) * 100;
            }
        } else {
            return null;
        }
    }

    /**
     * Gibt den Fortschritt des Indikators zurueck.
     * @return Integer|null Fortschritt
     */
    public function GetProgress() {
        //Ueberpruefung von min und max ob die Differenz 0 oder null ist
        if (($this->target - $this->base) != 0 and ($this->target - $this->base) != null) {
            return ($this->value - $this->base) / ($this->target - $this->base) * 100;
        } else {
            return null;
        }
    }

    /**
     * Gibt die ID des Indikators zurueck.
     * @return Integer|null ID
     */
    public function GetIdentifier() {
        return $this->ind_id;
    }

    /**
     * Gibt die ID der Perspektive zurueck.
     * @return Integer|null ID
     */
    public function GetPerspective() {
        return $this->per_id;
    }

    /**
     * Gibt den Namen des Indikators zurueck.
     * @return String Name
     */
    public function GetName() {
        return $this->name;
    }

    /**
     * Gibt die Beschreibung des Indikators zurueck.
     * @return String|null Beschreibung
     */
    public function GetDescription() {
        return $this->description;
    }

    /**
     * Gibt die Einheit des Indikators zurueck.
     * @return String|null Einheit
     */
    public function GetUnit() {
        return $this->unit;
    }

    /**
     * Gibt den Wert des Indikators zurueck.
     * @return Integer|null Wert
     */
    public function GetValue() {
        return $this->value;
    }

    /**
     * Gibt das Datum des Indikators zurueck.
     * @return Date Datum
     */
    public function GetDate() {
        return $this->date;
    }

    /**
     * Gibt das Minimum des Indikators zurueck.
     * @return Integer|null Minimum
     */
    public function GetMin() {
        return $this->min;
    }

    /**
     * Gibt das Maximum des Indikators zurueck.
     * @return Integer|null Maximum
     */
    public function GetMax() {
        return $this->max;
    }

    /**
     * Gibt das Ziel des Indikators zurueck.
     * @return Integer|null Ziel
     */
    public function GetTarget() {
        return $this->target;
    }

    /**
     * Gibt den Startwert des Indikators zurueck.
     * @return Integer|null Startwert
     */
    public function GetBase() {
        return $this->base;
    }

    /**
     * Gibt die Gewichtung des Indikators zurueck.
     * @return Integer Gewichtung
     */
    public function GetWeight() {
        return $this->weight;
    }

    /**
     * Gibt die Optimierungmethode (Maximierung: true / Minimierung: false) zurueck.
     * @return Boolean Optimierungmethode
     */
    public function GetMaximize() {
        return $this->maximize;
    }

    /**
     * Setzt den des Indikators Namen.
     * @param String $name Name
     */
    public function SetName($name = null) {
        if (isSet($name) && strLen($name) > 0) {
            $this->name = $name;
        }
    }

    /**
     * Setzt die Beschreibung des Indikators.
     * @param String|null $description Beschreibung
     */
    public function SetDescription($description = null) {
        $this->description = $description;
    }

    /**
     * Setzt die Einheit des Indikators.
     * @param String|null $unit Einheit
     */
    public function SetUnit($unit = null) {
        $this->unit = $unit;
    }

    /**
     * Setzt den Wert des Indikators.
     * @param Integer $value Wert
     */
    public function SetValue($value = null) {
        if (is_numeric($value) && $value >= 0) {
            $this->value = $value;
        }
    }

    /**
     * Setzt das Minimum des Indikators.
     * @param Integer $min Minimum
     */
    public function SetMin($min = null) {
        if (is_numeric($min) && $min >= 0) {
            $this->min = $min;
        }
    }

    /**
     * Setzt das Maximum des Indikators.
     * @param Integer $max Maximium
     */
    public function SetMax($max = null) {
        if (is_numeric($max) && $max >= 0) {
            $this->max = $max;
        }
    }

    /**
     * Setzt das Ziel des Indikators.
     * @param Integer $target Ziel
     */
    public function SetTarget($target = null) {
        if (is_numeric($target) && $target >= 0) {
            $this->target = $target;
        }
    }

    /**
     * Setzt den Startwert des Indikators.
     * @param Integer $base Startwert
     */
    public function SetBase($base = null) {
        if (is_numeric($base) && $base >= 0) {
            $this->base = $base;
        }
    }

    /**
     * Setzt die Gewichtung des Indikators.
     * @param Integer $weight Gewichtung
     */
    public function SetWeight($weight = null) {
        if (is_numeric($weight) && $weight > 0) {
            $this->weight = $weight;
        }
    }

    /**
     * Setzt die Optimierungmethode (Maximierung: true / Minimierung: false).
     * @param Boolean $maximize Optimierungsrichtung
     */
    public function SetMaximize($maximize = true) {
        if (isSet($maximize)) {
            $this->maximize = $maximize;
        }
    }

}
?>