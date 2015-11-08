<?php
class coordinate {
    public $lat;
    public $lon;
    public $existance;
    public $name;
    
    public function __construct($lat,$lon,$existance,$name) {
        $this->lat = $lat;
        $this->lon = $lon;
        $this->existance = $existance;
        $this->name = $name;
    }
}

class geocoder {
    public $keyPhrases;
    
    public function __construct($keyPhrases) {
        $this->keyPhrases = $keyPhrases;
    }
    
    public function geocode() {
        $result = array();
        for ($i = 0; $i < count($this->keyPhrases); $i++) {
            #echo $this->keyPhrases[$i];
            $params = array(
                'geocode' => $this->keyPhrases[$i], // адрес
                'format'  => 'json',                          // формат ответа
                'results' => 1                               // количество выводимых результатов
            );
            $response = json_decode(file_get_contents('http://geocode-maps.yandex.ru/1.x/?' . http_build_query($params, '', '&')));
            if ($response->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
                $posit = $response->response->GeoObjectCollection->featureMember[0]->GeoObject->Point->pos;
                $coords = explode(" ", $posit);
                $coordinate = new coordinate ($coords[1],$coords[0],1,$this->keyPhrases[$i]);
                $result[] = $coordinate;
            }
            else {
                $coordinate = new coordinate (0,0,0,"");
                $result[] = $coordinate;
            }
        }
        return $result;
    }
}

// testing
//$listo[0] = 'Санкт-Петербург';
//$listo[1] = 'Киров';
//$geoca = new geocoder($listo);
//$result = $geoca->geocode();
//echo $result[1]->name;
//echo $result[1]->lat;
?>