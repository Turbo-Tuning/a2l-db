<?php

class xmlQuery{
    var $xml;

    public function __construct($xmlFile){
        $this->xml = simplexml_load_string(gzdecode(file_get_contents($xmlFile)));
    }

    public function getListOfCharacteristics(){
        $found = $this->xml->xpath('/A2L/PROJECT/MODULE/CHARACTERISTIC');
        if((is_array($found)) and (count($found) > 0)){
            foreach($found as $key => $val){
                $val = json_decode(json_encode((array)$val), TRUE);
                $ret[] = $val['@attributes']['shortDesc'];
            }
            return $ret;
        } else {
            return '';
        }
        
    }

    public function getCharacteristicByName($name){
        $found = $this->xml->xpath('/A2L/PROJECT/MODULE/CHARACTERISTIC[@shortDesc="'.$name.'"]');
        return json_decode(json_encode((array)$found), TRUE);
    }
}