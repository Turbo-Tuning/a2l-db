<?php

class a2lArrayManager{

    var $compu_types = array('COEFFS', 'COMPU_TAB_REF');
    var $characteristics = array();
    var $measurements = array();
    var $compu_methods = array();
    var $record_layouts = array();
    var $functions = array();
    var $xmldb;

    public function __construct($xmlbuff){
        $time_start = microtime(true);
        $this->xmldb = new xmlQuery($xmlbuff);
        
        $this->populateArrays();
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        echo ('a2lArrayManager Time: '.$execution_time.' Secs'.PHP_EOL);
        //$this->Test();
    }

    public function getCodeOffset(){
        $yy = $this->xmldb->getListOf('MEMORY_SEGMENT');
        if(count($yy) > 0){
            foreach($yy as $yv){
                $xx = $this->xmldb->getParameterByName('MEMORY_SEGMENT', $yv);
                $offset = 0;
                if(!isset($xx[0]['@attributes']['shortDesc'])){
                    $offset = 1;
                }
                if($xx[0]['text'][$offset] == 'CODE'){
                    if(isset($xx[0]['IF_DATA']['ADDRESS_MAPPING'])){
                        return $xx[0]['IF_DATA']['ADDRESS_MAPPING'];
                    } elseif(isset($xx[0]['IF_DATA'][0]['ADDRESS_MAPPING'])) {
                        return $xx[0]['IF_DATA'][0]['ADDRESS_MAPPING'];
                    } else {
                        return $xx[0]['text'][3];
                    }
                }
            }
        } else {
            $yy = $this->xmldb->getListOf('MEMORY_LAYOUT');
            if(count($yy) > 0){
                if(count($yy)<8){
                    $zz = $this->xmldb->getParameterByName('MEMORY_LAYOUT', $yy[0]);
                    if(isset($zz[0]['@attributes']['shortDesc'])){
                        $offset = 0;
                    } else {
                        $offset = 1;
                    }
                    return $zz[0]['text'][$offset];
                } else {
                    return $yy[1];
                }
                
            }
        }
        
        return 0;
    }

    public function getCompuMethod($name){
        if(isset($this->compu_methods[$name])){
            return $this->compu_methods[$name];
        } else {
            $cm = new CompuMethod;
            $cm->name = $name;
            return $cm;
        }
        
        /*$ret = $this->xmldb->getParameterByName('COMPU_METHOD', $name);
        $cm = new CompuMethod;
        $cm->name = $ret[0]['@attributes']['shortDesc'];
        if(isset($ret[0]['@attributes']['longDesc'])){
            $k=0;
            $cm->longDesc = $ret[0]['@attributes']['longDesc'];
            $cm->conversionType = $ret[0]['text'][0];
            $cm->format = $ret[0]['text'][1];
            if(in_array($ret[0]['text'][2], $this->compu_types)){
                $cm->uom = '';
            } else {
                $cm->uom = $ret[0]['text'][2];
                $k=1;
            }
            
            $cm->compu_type = $ret[0]['text'][2+$k];
            if($cm->compu_type == "COEFFS"){
                $cm->coeff_a = $ret[0]['text'][3+$k];
                $cm->coeff_b = $ret[0]['text'][4+$k];
                $cm->coeff_c = $ret[0]['text'][5+$k];
                $cm->coeff_d = $ret[0]['text'][6+$k];
                $cm->coeff_e = $ret[0]['text'][7+$k];
                $cm->coeff_f = $ret[0]['text'][8+$k];
            } else {
                if(!in_array($cm->compu_type, $this->compu_types)){
                    Msg('not compu_type? '.$cm->compu_type);
                }       
            }
        } else {
            $cm->longDesc = $ret[0]['text'][0];
            $cm->conversionType = $ret[0]['text'][1];
            $cm->format = $ret[0]['text'][2];
            $cm->uom = is_array($ret[0]['text'][3]) ? '' : $ret[0]['text'][3];
            $cm->compu_type = $ret[0]['text'][4];
            if($cm->compu_type == "COEFFS"){
                $cm->coeff_a = $ret[0]['text'][5];
                $cm->coeff_b = $ret[0]['text'][6];
                $cm->coeff_c = $ret[0]['text'][7];
                $cm->coeff_d = $ret[0]['text'][8];
                $cm->coeff_e = $ret[0]['text'][9];
                $cm->coeff_f = $ret[0]['text'][10];
            } else {
                if(!in_array($cm->compu_type, $this->compu_types)){
                    Msg('not compu_type? '.$cm->compu_type);
                }
            }
        }
        
        return $cm; */
    }

    public function getMeasurement($name){
        if(isset($this->measurements[$name])){
            return $this->measurements[$name];
        } else {
            $ms = new Measurement;
            $ms->name = $name;
            return $ms;
        }
        
        $ret = $this->xmldb->getParameterByName('MEASUREMENT', $name);
        $ms = new Measurement;

        if(isset($ret[0])){
            $ms->name = $ret[0]['@attributes']['shortDesc'];
            $ms->desc = $ret[0]['@attributes']['longDesc'];
            if(!isset($ret[0]['text'][0])){
                //Msg('data_type error');
            }
            $ms->data_type = $ret[0]['text'][0];
            $ms->compu_method = $ret[0]['text'][1];
            $ms->resolution = $ret[0]['text'][2];
            $ms->accuracy = $ret[0]['text'][3];
            $ms->lowerLimit = $ret[0]['text'][4];
            $ms->upperLimit = $ret[0]['text'][5];
            return $ms;
        } else {
            //do nothing
            $ms->name = $name;
            return $ms;
        }
    }

    public function getModPar(){
        return $this->xmldb->getModPar();
    }

    public function getEPK(){
        $epk = $this->xmldb->getEPK();
        $epk = json_decode(json_encode((array)$epk), TRUE);
        if(is_array($epk)){
            if(count($epk)>0){
                return $epk[0];
            } else {
                return "@#%&";
            }
        }
        return $epk;
    }

    public function getEPKAddr(){
        $addr = hexdec($this->xmldb->getEPKAddr());
        $off = hexdec($this->getCodeOffset());
        if($addr > $off){
            $addr = $addr-$off;
        }
        return $addr;
    }

    public function getByteOrder(){
        return $this->xmldb->getByteOrder();
    }

    public function getRecordLayout($name){
        $key = array_search($name, array_column(array_column($this->record_layouts, '@attributes'), 'shortDesc'));
        $ret = $this->record_layouts[$key];
        return $ret;
    }

    public function getFunctionDesc($name){
        $func = '';
        if(isset($name['@attributes'])){
            $name = $name['@attributes']['shortDesc'];
        }
        if(count($this->functions)>0){
            if(isset($this->functions[$name])){
                $func = $this->functions[$name];
            }
        } else {

        }
        return $func;
    }

    public function getFunctionViaFunction($name){
        return $this->getFunctionDesc($name);
    }

    private function populateArrays(){
        //Msg('populateCharacteristics');
        $this->populateCharacteristics();
        //Msg('populate Measurements');
        $this->populateMeasurements();
        $this->populateCompuMethods();
        $this->populateRecordLayouts();
        //Msg('populate Functions');
        $this->populateFunctions();
        //Msg('finished populating');

    }

    private function populateFunctions(){
        $lst = $this->xmldb->getAllParameters('FUNCTION');
        //$this->functions = $lst;
        foreach($lst as $ls){
            if(isset($ls['@attributes']['longDesc'])){
                $func = $ls['@attributes']['shortDesc'].' ('.$ls['@attributes']['longDesc'].')';
            } else {
                $func = $ls['@attributes']['shortDesc'];
            }
            if(isset($ls['DEF_CHARACTERISTIC'])){
                $this->functions[$ls['DEF_CHARACTERISTIC']['@attributes']['shortDesc']] = $func;
                $this->functions[$ls['DEF_CHARACTERISTIC']['@attributes']['longDesc']] = $func;
                if(isset($ls['DEF_CHARACTERISTIC']['text'])){
                    $text = $ls['DEF_CHARACTERISTIC']['text'];
                    if(is_array($text)){
                        foreach($text as $txt){
                            $this->functions[$txt] = $func;
                        }
                    } elseif(is_null($text)){
                        //do nothing
                    } else {
                        $this->functions[$text] = $func;
                    }
                }
            }
        }
    }

    private function populateRecordLayouts(){
        $lst = $this->xmldb->getAllParameters('RECORD_LAYOUT');
        $this->record_layouts = $lst;
    }

    private function populateCompuMethods(){
        $lst = $this->xmldb->getAllParameters('COMPU_METHOD');
        
        while(count($lst) > 0){
            $ret = array();
            $ret[] = array_shift($lst);
            $cm = new CompuMethod;
            $cm->name = $ret[0]['@attributes']['shortDesc'];
            if(isset($ret[0]['@attributes']['longDesc'])){
                $k=0;
                $cm->longDesc = $ret[0]['@attributes']['longDesc'];
                $cm->conversionType = $ret[0]['text'][0];
                $cm->format = $ret[0]['text'][1];
                if(in_array($ret[0]['text'][2], $this->compu_types)){
                    $cm->uom = '';
                } else {
                    $cm->uom = $ret[0]['text'][2];
                    $k=1;
                }
                
                if(isset($ret[0]['text'][2+$k])){
                    $cm->compu_type = $ret[0]['text'][2+$k];
                    if($cm->compu_type == "COEFFS"){
                        $cm->coeff_a = $ret[0]['text'][3+$k];
                        $cm->coeff_b = $ret[0]['text'][4+$k];
                        $cm->coeff_c = $ret[0]['text'][5+$k];
                        $cm->coeff_d = $ret[0]['text'][6+$k];
                        $cm->coeff_e = $ret[0]['text'][7+$k];
                        $cm->coeff_f = $ret[0]['text'][8+$k];
                    } else {
                        if(!in_array($cm->compu_type, $this->compu_types)){
                            //Msg('not compu_type? '.$cm->compu_type);
                        }       
                    }
                } else {
                    //Msg('Check compu_method');
                    $cm->compu_type = $ret[0]['text'][0];
                    $cm->formula = $ret[0]['FORMULA']['text'];
                }
            } else {
                $cm->longDesc = $ret[0]['text'][0];
                $cm->conversionType = $ret[0]['text'][1];
                $cm->format = $ret[0]['text'][2];
                $cm->uom = is_array($ret[0]['text'][3]) ? '' : $ret[0]['text'][3];
                $cm->compu_type = $ret[0]['text'][4];
                if($cm->compu_type == "COEFFS"){
                    $cm->coeff_a = $ret[0]['text'][5];
                    $cm->coeff_b = $ret[0]['text'][6];
                    $cm->coeff_c = $ret[0]['text'][7];
                    $cm->coeff_d = $ret[0]['text'][8];
                    $cm->coeff_e = $ret[0]['text'][9];
                    $cm->coeff_f = $ret[0]['text'][10];
                } else {
                    if(!in_array($cm->compu_type, $this->compu_types)){
                        //Msg('not compu_type? '.$cm->compu_type);
                    }
                }
            }
            //$cm = json_decode(json_encode((array)$cm), TRUE);
            $this->compu_methods[$cm->name] = $cm;
        }
    }

    private function populateMeasurements(){
        $lst = $this->xmldb->getAllParameters('MEASUREMENT');
        
        while(count($lst) > 0){
            $ret = array();
            $ret[] = array_shift($lst);
            $ms = new Measurement;

            if(isset($ret[0])){
                $ms->name = $ret[0]['@attributes']['shortDesc'];
                $ms->desc = $ret[0]['@attributes']['longDesc'];
                if(!isset($ret[0]['text'][0])){
                    //Msg('data_type error');
                }
                $ms->data_type = $ret[0]['text'][0];
                $ms->compu_method = $ret[0]['text'][1];
                $ms->resolution = $ret[0]['text'][2];
                $ms->accuracy = $ret[0]['text'][3];
                $ms->lowerLimit = $ret[0]['text'][4];
                $ms->upperLimit = $ret[0]['text'][5];
                //return $ms;
            } else {
                //Msg('different');
            }
            //$ms = json_decode(json_encode((array)$ms), TRUE);
            $this->measurements[$ms->name] = $ms;
        }
    }

    private function populateCharacteristics(){
        $lst = $this->xmldb->getAllParameters('CHARACTERISTIC');
        
        while(count($lst) > 0){
            $ret = array();
            $ret[] = array_shift($lst);
            $cha = new Characteristic;
            $cha->name = $ret[0]['@attributes']['shortDesc'];
            $cha->desc = $ret[0]['@attributes']['longDesc'];
            $cha->type = $ret[0]['text'][0];
            $cha->addr = $ret[0]['text'][1];
            $cha->record_layout = $ret[0]['text'][2];
            $cha->maxDiff = $ret[0]['text'][3];
            $cha->compu_method = $ret[0]['text'][4];
            $cha->lowerLimit = $ret[0]['text'][5];
            $cha->upperLimit = $ret[0]['text'][6];
            if(isset($ret[0]['FORMAT'])) {
                $cha->format = $ret[0]['FORMAT'];
            } else {
                //Msg('No FORMAT?');
            }
            if(isset($ret[0]['FUNCTION_LIST'])) $cha->function = @$ret[0]['FUNCTION_LIST'];

            if(isset($ret[0]['AXIS_DESCR'])){
                if(isset($ret[0]['AXIS_DESCR'][1])){
                    $axis_x = new AxisDescr;
                    $axis_x->type = $ret[0]['AXIS_DESCR'][0]['@attributes']['shortDesc'];
                    if(isset($ret[0]['AXIS_DESCR'][0]['@attributes']['longDesc'])){
                        $axis_x->measurement = $ret[0]['AXIS_DESCR'][0]['@attributes']['longDesc'];
                        $axis_x->compu_method = $ret[0]['AXIS_DESCR'][0]['text'][0];
                        $axis_x->max_axis_points = $ret[0]['AXIS_DESCR'][0]['text'][1];
                        $axis_x->lowerLimit = $ret[0]['AXIS_DESCR'][0]['text'][2];
                        $axis_x->upperLimit = $ret[0]['AXIS_DESCR'][0]['text'][3];
                    } else {
                        $axis_x->measurement = $ret[0]['AXIS_DESCR'][0]['text'][0];
                        $axis_x->compu_method = $ret[0]['AXIS_DESCR'][0]['text'][1];
                        $axis_x->max_axis_points = $ret[0]['AXIS_DESCR'][0]['text'][2];
                        $axis_x->lowerLimit = $ret[0]['AXIS_DESCR'][0]['text'][3];
                        $axis_x->upperLimit = $ret[0]['AXIS_DESCR'][0]['text'][4];
                    }
                    
                    $cha->axis_descriptions->Add($axis_x);
                    
                    $axis_y = new AxisDescr;
                    $axis_y->type = $ret[0]['AXIS_DESCR'][1]['@attributes']['shortDesc'];
                    if(isset($ret[0]['AXIS_DESCR'][1]['@attributes']['longDesc'])){
                        $axis_y->measurement = $ret[0]['AXIS_DESCR'][1]['@attributes']['longDesc'];
                        $axis_y->compu_method = $ret[0]['AXIS_DESCR'][1]['text'][0];
                        $axis_y->max_axis_points = $ret[0]['AXIS_DESCR'][1]['text'][1];
                        $axis_y->lowerLimit = $ret[0]['AXIS_DESCR'][1]['text'][2];
                        $axis_y->upperLimit = $ret[0]['AXIS_DESCR'][1]['text'][3];
                    } else {
                        $axis_y->measurement = $ret[0]['AXIS_DESCR'][1]['text'][0];
                        $axis_y->compu_method = $ret[0]['AXIS_DESCR'][1]['text'][1];
                        $axis_y->max_axis_points = $ret[0]['AXIS_DESCR'][1]['text'][2];
                        $axis_y->lowerLimit = $ret[0]['AXIS_DESCR'][1]['text'][3];
                        $axis_y->upperLimit = $ret[0]['AXIS_DESCR'][1]['text'][4];
                    }
                    
                    $cha->axis_descriptions->Add($axis_y);
                } else {
                    $axis_x = new AxisDescr;
                    $axis_x->type = $ret[0]['AXIS_DESCR']['@attributes']['shortDesc'];
                    if(isset($ret[0]['AXIS_DESCR']['@attributes']['longDesc'])){
                        $axis_x->measurement = $ret[0]['AXIS_DESCR']['@attributes']['longDesc'];
                        $axis_x->compu_method = $ret[0]['AXIS_DESCR']['text'][0];
                        $axis_x->max_axis_points = $ret[0]['AXIS_DESCR']['text'][1];
                        $axis_x->lowerLimit = $ret[0]['AXIS_DESCR']['text'][2];
                        $axis_x->upperLimit = $ret[0]['AXIS_DESCR']['text'][3];
                    } else {
                        $axis_x->measurement = $ret[0]['AXIS_DESCR']['text'][0];
                        $axis_x->compu_method = $ret[0]['AXIS_DESCR']['text'][1];
                        $axis_x->max_axis_points = $ret[0]['AXIS_DESCR']['text'][2];
                        $axis_x->lowerLimit = $ret[0]['AXIS_DESCR']['text'][3];
                        $axis_x->upperLimit = $ret[0]['AXIS_DESCR']['text'][4];
                    }
                    
                    $cha->axis_descriptions->Add($axis_x);
                }
            }
            //$cha = json_decode(json_encode((array)$cha), TRUE);
            $this->characteristics[$cha->name] = $cha;
        }
    }
}
