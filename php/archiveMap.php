<?php

class archiveMap {
    public $public_id;
    public $jsFile;
    public $timeStamp;

    public function __construct($public_id,$jsFile,$timeStamp) {
        $this->public_id = $public_id;
        $this->jsFile = $jsFile;
        $this->timeStamp = $timeStamp;
    }
    
}

?>