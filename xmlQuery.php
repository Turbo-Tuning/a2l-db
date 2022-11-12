<?php

class xmlQuery{
    var $xml;
    var $functionArr = array();

    public function __construct($xmlFile){
        if(file_exists($xmlFile)){
            $this->xml = simplexml_load_string(gzdecode(file_get_contents($xmlFile)));
        } else {
            $this->xml = simplexml_load_string($xmlFile);
        }
        
    }

    public function getFunctionForCharacteristic($name){
        $found = $this->xml->xpath('//FUNCTION');
        $found = json_decode(json_encode((array)$found), TRUE);
        //
        
    }

    public function getAllParameters($parameter){
        $found = $this->xml->xpath('//'.strtoupper($parameter));
        return json_decode(json_encode((array)$found), TRUE);
    }

    public function getListOf($parameter){
        $ret = array();
        $found = $this->xml->xpath('//'.strtoupper($parameter));
        $found = json_decode(json_encode((array)$found), TRUE);
        foreach($found as $key => $val){
            if (isset($val['@attributes']['shortDesc'])){
                $ret[] = $val['@attributes']['shortDesc'];
            } else {
                $ret[] = $val[0];
            }
            
        }
        return $ret;
    }

    public function getParameterByName($parameter, $name){
        $found = $this->xml->xpath('//'.strtoupper($parameter).'[@shortDesc="'.$name.'"]');
        return json_decode(json_encode((array)$found), TRUE);
    }

    public function getModPar(){
        $found = $this->xml->xpath('//MOD_PAR');
        $found = json_decode(json_encode((array)$found), TRUE);
        return $found;
    }

    public function getEPK(){
        $epk = '';
        $found = $this->xml->xpath('//MOD_PAR');
        $found = json_decode(json_encode((array)$found), TRUE);
        if(isset($found[0]['EPK'])){
            $epk = @$found[0]['EPK'];
        }
        
        return $epk;
    }

    public function getEPKAddr(){
        $addr='';
        $found = $this->xml->xpath('//MOD_PAR');
        $found = json_decode(json_encode((array)$found), TRUE);
        if(isset($found[0]['ADDR_EPK'])){
            $addr = @$found[0]['ADDR_EPK'];
        }
        return $addr;
    }

    public function getByteOrder(){
        $found = $this->xml->xpath('//MOD_COMMON');
        $found = json_decode(json_encode((array)$found), TRUE);
        if(isset($found[0])){
            return $found[0]['BYTE_ORDER'];
        }
        return '';
    }
}