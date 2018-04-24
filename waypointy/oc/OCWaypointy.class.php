<?php
class OCWaypointy {
    // database session opened with DBPConnect();
    private $dblink;
    // report current activity to stdout
    private $verbose;

    public function __construct($dblink, $verbose) {
        $this->dblink = $dblink;
        $this->verbose = $verbose;
    }

    /**
     * get distinct kraj from gk-waypointy
     * update gk-waypointy-country with result
     * 
     * get distinct typ from gk-waypointy
     * update gk-waypointy-type with result
     */
    public function updateTranslations() {
        $this->mustHaveTable("gk-waypointy-type");
        $this->mustHaveTable("gk-waypointy-country");
        //~ kraj
        $countries = $this->getWaypointyCountries();
        $this->updateCountryTranslations($countries);
        //~ typ
        $typs = $this->getWaypointyTyps();
        $this->updateTypeTranslations($typs);
    }

    public function generateTranslations() {
        $enCountries = $this->getEnglishCountries();
        $enCacheTypes = $this->getEnglishCacheTypes();
        echo "<html><head></head><body>\n";
        $this->generateTranslationsSerie($enCountries, "Countries");
        $this->generateTranslationsSerie($enCacheTypes, "Cache-types");
        echo "</body></html>\n";
    }

    public function reportStateCountry() {
        $nbCTranslation = $this->getCountryTranslationsCount();
        $nbCMissing = $this->getMissingCountryTranslationsCount();
        if ($nbCMissing > 0) {
            echo " X $nbCMissing missing country translation(s) (total:$nbCTranslation) please update gk-waypointy-country.sql and database accordingly\n";
            return;
        }
        echo " - $nbCTranslation country translations - OK\n";
    }

    public function reportStateType() {
        $nbTTranslation = $this->getTypeTranslationsCount();
        $nbTMissing = $this->getMissingTypeTranslationsCount();
        if ($nbTMissing > 0) {
            echo " X $nbTMissing missing type translation(s) (total:$nbTTranslation) please update gk-waypointy-type.sql and database accordingly\n";
            return;
        }
        echo " - $nbTTranslation type translations - OK\n";
    }

    private function generateTranslationsSerie($values, $valuesDescription) {
        echo "<h1>", htmlspecialchars(count($values)." ".$valuesDescription), "</h1>\n";
        foreach ($values as &$val) {
            echo "{t}",htmlspecialchars($val),"{/t}<br/>\n";
        }        
    }

    private function mustHaveTable($tableName) {
       if ($tableName == '') {
           throw new Exception("table name expected");
       }
       $sql = "SHOW TABLES LIKE '".$tableName."'";
       $result = mysqli_query($this->dblink, $sql);
       $hasTable = ($result && $result->num_rows == 1);
       if (!$hasTable) {
         if ($this->verbose) {
           echo " X ", $tableName, " expected!\n";
         }
         throw new Exception("table $tableName expected");
        }
    }

    private function resultToArray($result) {
        $resultArray = [];
        if (!$result) {
            return $resultArray;
        }
        while($row = $result->fetch_row()) {
            $resultArray[] = $row[0];
        }
        return $resultArray;
    }

    private function getWaypointyCountries() {
        $sql = "SELECT DISTINCT kraj FROM `gk-waypointy`";
        $result = mysqli_query($this->dblink, $sql);
        $hasCountries = ($result && $result->num_rows >= 0);
        if ($this->verbose && $hasCountries) {
            echo " - gk-waypointy has ", $result->num_rows, " (DISTINCT) countries ('kraj')\n";
        }
        $wpCountries = $this->resultToArray($result);
        $result->close();
        return $wpCountries;
    }

    private function getEnglishCountries() {
        $sql = "SELECT DISTINCT country FROM `gk-waypointy-country`";
        $result = mysqli_query($this->dblink, $sql);
        $wpCountries = $this->resultToArray($result);
        $result->close();
        return $wpCountries;
    }

    private function getEnglishCacheTypes() {
        $sql = "SELECT DISTINCT cache_type FROM `gk-waypointy-type`";
        $result = mysqli_query($this->dblink, $sql);
        $wpCT = $this->resultToArray($result);
        $result->close();
        return $wpCT;
    }

    private function getWaypointyTyps() {
        $sql = "SELECT DISTINCT typ FROM `gk-waypointy`";
        $result = mysqli_query($this->dblink, $sql);
        $hasTyps = ($result && $result->num_rows >= 0);
        if ($this->verbose && $hasTyps) {
            echo " - gk-waypointy has ", $result->num_rows, " (DISTINCT) types ('typ')\n";
        }
        $wpTyps = $this->resultToArray($result);
        $result->close();
        return $wpTyps;
    }

    private function getCountryTranslationsCount() {
        $sql = "SELECT * FROM `gk-waypointy-country`";
        $result = mysqli_query($this->dblink, $sql);
        if (!$result) {
            return 0;
        }
        return $result->num_rows;
    }

    private function getTypeTranslationsCount() {
        $sql = "SELECT * FROM `gk-waypointy-type`";
        $result = mysqli_query($this->dblink, $sql);
        if (!$result) {
            return 0;
        }
        return $result->num_rows;
    }

    private function getMissingCountryTranslationsCount() {
        $sql = "SELECT * FROM `gk-waypointy-country` WHERE country IS NULL";
        $result = mysqli_query($this->dblink, $sql);
        if (!$result) {
            return 0;
        }
        return $result->num_rows;
    }

    private function getMissingTypeTranslationsCount() {
        $sql = "SELECT * FROM `gk-waypointy-type` WHERE cache_type IS NULL";
        $result = mysqli_query($this->dblink, $sql);
        if (!$result) {
            return 0;
        }
        return $result->num_rows;
    }

    private function updateCountryTranslations($countries) {
        $nbBefore = $this->getCountryTranslationsCount();
        foreach ($countries as &$country) {
            $sql = "INSERT IGNORE INTO `gk-waypointy-country` ( `kraj`) VALUES ('" . $country . "')";
            mysqli_query($this->dblink, $sql);
        }
        $nbAfter = $this->getCountryTranslationsCount();
        $nbAdded = $nbAfter - $nbBefore;
        if ($this->verbose && $nbAdded > 0) {
            echo " - ", $nbAdded, " countries added\n";
        }
    }

    private function updateTypeTranslations($types) {
        $nbBefore = $this->getTypeTranslationsCount();
        foreach ($types as &$typ) {
            $sql = "INSERT IGNORE INTO `gk-waypointy-type` ( `typ`) VALUES ('" . $typ . "')";
            mysqli_query($this->dblink, $sql);
        }
        $nbAfter = $this->getTypeTranslationsCount();
        $nbAdded = $nbAfter - $nbBefore;
        if ($this->verbose && $nbAdded > 0) {
            echo " - ", $nbAdded, " types added\n";
        }
    }
}