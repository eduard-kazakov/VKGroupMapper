<?php
include "databaseManager.php";
include "geocoder.php";
//include "geoObject.php";

include "archiveMap.php";
include "geoJsonGenerator.php";

class vkDatabaseManager extends databaseManager {
    
    private $cityNameColumn = 'name';
    private $numColumn = 'count';
    private $schemaForPublicTable = 'groups';
    private $schemaForGeocodingTable = 'info';
    private $geocodingTableName = 'geocoding';
    private $geocodingLatColumn = 'lat';
    private $geocodingLonColumn = 'lon';
    private $geocodingNameColumn = 'name';
    private $schemaForArchive = 'info';
    private $archiveTableName = 'archive';
    private $archivePublicIdColumn = 'public_id';
    private $archiveTimeStampColumn = 'timestamp';
    private $archiveJsFileColumn = 'jsfile';
    

    
    public function createTableOfPublicCities ($tableName, $cities) {
        $uniqueCities = array_unique($cities);
        $countCities = array_count_values($cities);
        
        // Создаем таблицу
        $query = "CREATE TABLE $this->schemaForPublicTable.$tableName (id SERIAL NOT NULL, $this->cityNameColumn text, $this->numColumn real) WITH (OIDS = FALSE); ALTER TABLE $this->schemaForPublicTable.$tableName OWNER TO postgres;";
        $this->doQuery($query);
        
        foreach ($uniqueCities as $city) {
            $query = "INSERT INTO $this->schemaForPublicTable.$tableName ($this->cityNameColumn,$this->numColumn) VALUES ('$city',$countCities[$city])";
            $this->doQuery($query);
        }
    }
    
    
    public function getAllCitiesOfPublic ($TableName) {
        $query = "SELECT $this->cityNameColumn FROM $this->schemaForPublicTable.$TableName;";
        //echo $query;
        $result = $this->doQuery ($query);
        
        $cities = array ();
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            //echo $line;
            foreach ($line as $col_value) {
                //echo "$col_value";
                $cities[] = $col_value;
            }
        }
        return $cities;
    }
    
    public function getUncodedCities ($TableName) {
        //select name from groups.test_group_01_01_2015 where name not in (SELECT name from info.geocoding)
        $query = "SELECT $this->cityNameColumn FROM $this->schemaForPublicTable.$TableName WHERE $this->cityNameColumn NOT IN (SELECT $this->geocodingNameColumn FROM $this->schemaForGeocodingTable.$this->geocodingTableName);";
        $result = $this->doQuery ($query);
        $cities = array ();
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            foreach ($line as $col_value) {
                $cities[] = $col_value;
            }
        }
        return $cities;       
    }
    
    public function addMapToArchive ($public_id, $jsName, $timeStamp) {
        //$map = new archiveMap($public_id,$jsName,$timeStamp);
        $query = "INSERT INTO $this->schemaForArchive.$this->archiveTableName ($this->archivePublicIdColumn,$this->archiveTimeStampColumn,$this->archiveJsFileColumn) VALUES ('$public_id','$timeStamp','$jsName')";
        $this->doQuery($query);
    }
    
    public function getArchiveMaps () {
        $maps = array ();
        
        $query = "SELECT $this->archivePublicIdColumn,$this->archiveTimeStampColumn,$this->archiveJsFileColumn FROM $this->schemaForArchive.$this->archiveTableName";
        $result = $this->doQuery($query);
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            $map = new archiveMap($line[$this->archivePublicIdColumn],$line[$this->archiveJsFileColumn],$line[$this->archiveTimeStampColumn]);
            $maps [] = $map;
        }
        return $maps;
    }
    
    public function getDecodedCities ($TableName) {
        $query = "SELECT $this->cityNameColumn FROM $this->schemaForPublicTable.$TableName WHERE $this->cityNameColumn IN (SELECT $this->geocodingNameColumn FROM $this->schemaForGeocodingTable.$this->geocodingTableName);";
        $result = $this->doQuery ($query);
        $cities = array ();
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            foreach ($line as $col_value) {
                $cities[] = $col_value;
            }
        }
        return $cities;       
    }
    
    public function geocodeCitiesAndAddToTable ($cities) {
        $geocoderInstance = new geocoder ($cities);
        $result = $geocoderInstance->geocode();
        //return $result;
        foreach ($result as $object) {
            if ($object->existance == 1) {
                $query = "INSERT INTO $this->schemaForGeocodingTable.$this->geocodingTableName ($this->geocodingNameColumn,$this->geocodingLatColumn,$this->geocodingLonColumn) VALUES ('$object->name',$object->lat,$object->lon);";
                //echo $query;
                $this->doQuery($query);
            }
        }
    }
    
    public function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    public function getGeoObjectsArrayFromTable ($TableName) {
        // SELECT groups.test_group_01_01_2015.id, groups.test_group_01_01_2015.name, info.geocoding.lat, info.geocoding.lon FROM groups.test_group_01_01_2015 INNER JOIN info.geocoding ON groups.test_group_01_01_2015.name = info.geocoding.name
        
        $query = "SELECT $this->schemaForPublicTable.$TableName.id, $this->schemaForPublicTable.$TableName.$this->cityNameColumn, $this->schemaForGeocodingTable.$this->geocodingTableName.$this->geocodingLatColumn, $this->schemaForGeocodingTable.$this->geocodingTableName.$this->geocodingLonColumn, $this->schemaForPublicTable.$TableName.$this->numColumn FROM $this->schemaForPublicTable.$TableName INNER JOIN $this->schemaForGeocodingTable.$this->geocodingTableName ON $this->schemaForPublicTable.$TableName.$this->cityNameColumn = $this->schemaForGeocodingTable.$this->geocodingTableName.$this->geocodingNameColumn";
        //echo $query;
        $result = $this->doQuery($query);
        $geoObjectsArray = array ();
        while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
            if ((!empty ($line["id"])) and (!empty ($line["$this->cityNameColumn"])) and (!empty ($line["$this->geocodingLatColumn"])) and (!empty ($line["$this->geocodingLonColumn"])) and (!empty ($line["$this->numColumn"]))) {
                $geoObj = new geoObject ($line["id"],$line["$this->cityNameColumn"],$line["$this->geocodingLatColumn"],$line["$this->geocodingLonColumn"],$line["$this->numColumn"]);
                $geoObjectsArray[] = $geoObj;
            }
        }
        return $geoObjectsArray;
    }
    
}

?>