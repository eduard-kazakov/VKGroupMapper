<?php
class sagaIface {
    private $sagaCMDPath = '';

    public function __construct($path) {
        $this->sagaCMDPath = $path;
    }
    
    // point statistics by polygons
    public function pointsStatsByPolygons ($pointsPath,$polyPath,$statsPolyPath) {
        $cmd = "$this->sagaCMDPath shapes_polygons 4 -POINTS $pointsPath -FIELDS count -POLYGONS $polyPath -STATISTICS $statsPolyPath -NUM -SUM";
        return exec($cmd);
    }
    
    public function shpToGeoJSON ($shpPath,$geoJsonPath) {
        $cmd = "$this->sagaCMDPath io_gdal 4 -SHAPES $shpPath -FILE $geoJsonPath -FORMAT 17";
        return exec($cmd);
    }
    
    public function geoJsonToShp ($geoJsonPath,$shpPath,$geomType) {
    // geomType: 1 - Point; 5 - Line; 9 - Polygon
        $cmd = "$this->sagaCMDPath io_gdal 3 -SHAPES $shpPath -FILES $geoJsonPath -GEOM_TYPE $geomType";
        return exec($cmd);
    }
}

//$saga = new sagaIface ("C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/apps/saga/saga_cmd.exe");
//$saga->geoJsonToShp ("C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/files/totoro.geojson","C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/files/totoro.shp",1);
//$stats = $saga->pointsStatsByPolygons ("C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/files/pts.shp","C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/files/atd.shp","C:/WWW/GeoportalNevsky/Extra/vkGroupMapper/files/stats.shp");

 
?>