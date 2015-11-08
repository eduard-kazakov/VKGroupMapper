<?php
class geoObject {
    public $id = 0;
    public $name = "";
    public $lat = 0.0;
    public $lon = 0.0;
    public $num = 0;
    
    public function __construct($id,$name,$lat,$lon,$num) {
        $this->id = $id;
        $this->name = $name;
        $this->lat = $lat;
        $this->lon = $lon;
        $this->num = $num;
    }
}
?>