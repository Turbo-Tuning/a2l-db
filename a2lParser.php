<?php

//fo

class a2lparser {
    var $db_dir = 'parser_a2l/db/';
    var $trenner = array(" ", "[", "]", "{", "}", "(", ")", ",", ";", "=", "\r", "\l", "/", '"');
    var $beginend = array('/begin', '/end');
    var $DataTypes = array(
        'UBYTE',
        'SBYTE',
        'UWORD',
        'SWORD',
        'ULONG',
        'SLONG',
        'FLOAT32_IEEE',
        'FLOAT64_IEEE',
        'FLOAT32_TASKING'
    );
    var $mainsections = array(
        //'A2ML',
        'AXIS_DESCR',
        //'AXIS_PTS',
        //'CALIBRATION_HANDLE',
        //'CALIBRATION_METHOD',
        'CHARACTERISTIC',
        'COMPU_METHOD',
        //'COMPU_TAB',
        //'COMPU_VTAB',
        //'ETK_XETK_ACCESS',
        'FUNCTION',
        //'GROUP',
        'HEADER',
        //'IF_DATA',
        'MEASUREMENT',
        'MEMORY_LAYOUT',
        'MEMORY_SEGMENT',
        'MODULE',
        'MOD_COMMON',
        'MOD_PAR',
        'PROJECT',
        //'QP_BLOB',
        //'RASTER',
        //'RECORD_SEGMENT',
        //'SEGMENT',
        //'SOURCE',
        //'TP_BLOB',
        //'VS_DEF'
    );
    var $go_silent = array('A2ML', 'CALIBRATION_HANDLE', 'CALIBRATION_METHOD', 'CAN_PARAM', 'CHECKSUM', 'CHECKSUM_PARAM', 'COMPU_VTAB',
    'DIAG_BAUD', 'DISTAB_CFG', 'ETK_XETK_ACCESS', 'FLASH_COPY', 'IF_DATA', 'PAGE_SWITCH', 'QP_BLOB', 'RASTER', 'RECORD_LAYOUT', 'SEED_KEY', 'SEGMENT', 'SOURCE', 'TP_BLOB');
    var $no_skip = array(
        'AXIS_DESCR', 'CHARACTERISTIC', 'COMPU_METHOD', 'FUNCTION', 'HEADER', 'MEASUREMENT', 'MEMORY_SEGMENT', 'MODULE', 'MOD_COMMON', 'MOD_PAR');
    var $excl_from_vars = array('AXIS_DESCR');
    var $keywords = array(
        'ALIGNMENT_BYTE', 'ALIGNMENT_WORD', 'ALIGNMENT_LONG', 'ADDR_EPK', 'ADDRESS_MAPPING', 'ASAP2_VERSION', 'BYTE_ORDER', 'CAN_ID_FIXED', 'COMPU_METHOD', 'CPU_TYPE', 'CUSTOMER_NO', 
        'DEPOSIT', 'ECU', 'ECU_ADDRESS', 'EPK', 'EXTENDED_LIMITS', 'FIRST_PID', 'FORMAT',
        'FUNCTION_LIST', 'IF_DATA', 'LENGTH', 'MEMORY_LAYOUT', 'PHONE_NO', 'PROJECT_NO', 'PAGE_SWITCH', 'RASTER', 
        'SYSTEM_CONSTANT', 'USER', 'VERSION');
    var $watch_vars = array('uom', 'longDesc');

    var $end = false;
    var $outFile;
    var $tokens;
    var $silent = false;
    var $root;
    var $curr;

    function __construct($tokens, $outFile) {
        $this->tokens = $tokens;
        //$this->root = new xmlTree($outFile);
        $this->outFile = $outFile;
        $this->root = new _BASETYPE;
		$this->curr = $this->root;
    }

    function __destruct() {
    }

    function Parse() {
        set_time_limit(0);

        //$this->tokens->MoveFirst();
        //Msg('Begin parse. ');

        //$this->root->AddNode('A2L', 'A2L', 'A2L');
        $data = ($this->RecursiveParse('A2L'));
        //$this->root = ($data);
        
        //$ser = gzdeflate(serialize($data));

        //file_put_contents($f, $ser);
        //return $this->getBuffer();
        return $data;
    }

    function RecursiveParse($type = '') {
        $silent = false;
        $skip = false;
        if ($type == '') {
            $item = new A2L;
            //$curr = $this->curr;	
        } elseif($type == 'FUNCTION') {
            $item = new FUNC;
        } else {
            $item = new $type;
        }
        $idx = 0; //index for Vars
        if(in_array($type, $this->no_skip)){
            $skip = true;
        }

        while ($this->end != true) {
            $Token = $this->get($skip);
            switch ($Token) {
            case "/begin":
                $section = $this->get($skip);

                if(!in_array($section, $this->mainsections)) {
                    $silent = true;
                } 
                
                if (in_array($section, $this->mainsections)) {
                    //new section
                    //$this->curr->add($section);
                    
                    $data = $this->RecursiveParse($section);

                    if(is_array($item->$section)){
                        $item->$section[] = $data;
                    } else {
                        //Msg('val '.$section);
                        
                        $item->$section = $data;
                    }
                }    
                break;
            case "/end":
                $section = $this->get($skip);
                if ($section == $type) {
                    if (!in_array($section, $this->mainsections)){
                        $silent = false;
                    }

                    //Msg('end '.$section);
                    return $item;
                    //$coll = new Collection;
                    //$this->curr->addItem($item);
                    //return $this->curr;
                } else {
                    if(in_array($section, $this->mainsections)){
                        Msg('Problem ending section '.$section);    
                    }
                }
                //return $item;
                break;
            default:
                if(!$silent){

                    if (in_array($Token, $this->keywords)) {
                        $data = $this->DoKeywords($Token);
                        if (is_object($data)) {
                            if (is_object($item->$Token)) {
                                $item->$Token = ($data); //add object
                                $idx+count($data);
                            }
                        } else {
                            $item->$Token = $data;
                            $idx++;
                        }
                    } else {
                        $x = $item->countVars();
                        if ($idx < $x) {
                            $varName = $item->Var($idx);
                            if(in_array($varName, $this->watch_vars)){
                                //special handling
                                switch($varName){
                                    case 'uom':
                                        if($Token == 'COEFFS'){
                                            $idx++;
                                            $varName = $item->Var($idx);
                                        }
                                        break;
                                    case 'longDesc':
                                        $longArr = array('CHARACTERISTIC' => array('MAP', 'CURVE', 'VALUE'),
                                                        'MEMORY_SEGMENT' => array('CODE', 'DATA', 'RESERVED', 'VARIABLES'),
                                                        'COMPU_METHOD' => array('FORM', 'RAT_FUNC', 'TAB_VERB'),
                                                        'MEASUREMENT' => $this->DataTypes);
                                        //echo $type.'<br/>';
                                        if(isset($longArr[$type])){
                                            if(in_array($Token, $longArr[$type])){
                                                $idx++;
                                                $varName = $item->Var($idx);
                                            } 
                                        }
                                        break;
                                }
                            } 
                            $item->$varName = $Token;
                            $idx++;
                        }
                    }
                }
            }
        }
        return $item;
    }

    function removeUnwanted($str) {
        $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
            'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
            'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
        $str = strtr($str, $unwanted_array);
        return $str;
    }

    private function DoKeywords($keyword) {
        $ret = '';
        switch ($keyword) {
            case 'ASAP2_VERSION':
                $ret = new ASAP2_VERSION;
                $ret->major = $this->get();
                $ret->minor = $this->get();
                break;
            case 'EXTENDED_LIMITS':
                $ret = new EXTENDED_LIMITS;
                $ret->var1 = $this->get();
                $ret->var2 = $this->get();
                break;
            case 'SYSTEM_CONSTANT':
                $ret = new SYSTEM_CONSTANT;
                $ret->name = $this->get();
                $ret->constant = $this->get();
                break;
            case 'IF_DATA':
                $ret = new IF_DATA;
                $ret->name = $this->get();
                $ret->type = $this->get();
                $ret->orig_addr = $this->get();
                break;
            case 'FUNCTION_LIST':
                $ret = new FUNC;
                
                break;
            default:
                //$ret = new _BASETYPE;
                $ret = $this->get();
        }
        return $ret;
    }

    public function Prt() {
        return $this->root->Prt();
    }

    public function getBuffer() {
        return $this->root->getBuffer();
    }

    public function get(bool $skip_empty = false) {
        $t = '';
        if($this->tokens->endToken === true){
            $this->end = true;
            return false;
        }

        if($skip_empty){
            do{
                $t = trim($this->tokens->getToken(), chr(34));    
            } while ($t == '');
        } else {
            $t = trim($this->tokens->getToken(), chr(34));
        }
        
        //} else {
        //    $t = '';
        //}
        return $t;
    }
}

?>