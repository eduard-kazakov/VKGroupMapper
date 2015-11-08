<?php
include "geoObject.php";
include "sagaIface.php";

class geoJsonGenerator {
    private $fileName;
    private $geoObjects;
    private $geoJsonGeneratedFlag = "False";
    private $atdShpFileName = "C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/files/atd.shp";
    
    public function __construct($fileName,$geoObjects) {
        $this->fileName = $fileName;
        $this->geoObjects = $geoObjects;
    }
    
    public function generateGeoJson () {
        $geoJsonFile = fopen($this->fileName, "w");
        $textLine = "{\"type\": \"FeatureCollection\",\"crs\": { \"type\": \"name\", \"properties\": { \"name\": \"urn:ogc:def:crs:OGC:1.3:CRS84\" } },";
        $textLine .= "\"features\": [";
        
        foreach ($this->geoObjects as $currentGeoObject) {
            $textLine .= "{ \"type\": \"Feature\", \"properties\": { \"id\": ";
            $textLine .= $currentGeoObject->id;
            $textLine .= ", \"count\": ";
            $textLine .= $currentGeoObject->num;
            $textLine .= "}, \"geometry\": { \"type\": \"Point\", \"coordinates\": [ ";
            $textLine .= $currentGeoObject->lon;
            $textLine .= ",";
            $textLine .= $currentGeoObject->lat;
            $textLine .= "] } },";
        }
        $textLine .= "]}";
        fwrite($geoJsonFile, $textLine); 
        fclose($geoJsonFile);
        $this->geoJsonGeneratedFlag = "True";
        
    }
    
    public function generateGeoJsonStatedWithPolygons ($resultFileName) {
        
        if ($this->geoJsonGeneratedFlag == "False") {
            
            return 0;
        };

        foreach (new DirectoryIterator('C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/temp') as $fileInfo) {
            if(!$fileInfo->isDot()) {
                unlink($fileInfo->getPathname());
            }
        }
        
        $saga = new sagaIface ("C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/apps/saga/saga_cmd.exe");
        $temp_name = "C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/temp/tempShapePoints.shp";
        
        $saga->geoJsonToShp($this->fileName,$temp_name,1);
        
        $temp_name2 = "C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/temp/tempShapePolygons.shp";
        
        $saga->pointsStatsByPolygons ($temp_name,$this->atdShpFileName,$temp_name2);
        
        $saga->shpToGeoJSON($temp_name2,$resultFileName);
        

    }
    
    public function geoJsonToJS ($jsonName, $jsName) {
        $d=file_get_contents($jsonName);
        $f=fopen($jsName, 'w+');
        fwrite($f, 'var statesData = '.$d);
        fclose($f);
    }
    
}

?>