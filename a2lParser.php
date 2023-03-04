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
        'FLOAT32_TASKING',
    );
    var $mainsections = array(
        'A2L',
        'AXIS_DESCR',
        //'AXIS_PTS',
        //'CALIBRATION_HANDLE',
        //'CALIBRATION_METHOD',
        'CHARACTERISTIC',
        'COMPU_METHOD',
        //'COMPU_TAB',
        //'COMPU_VTAB',
        //'ETK_XETK_ACCESS',
        'DEF_CHARACTERISTIC', 
        //'DISTAB_CFG',
        'FUNC', //stands for FUNCTION, which is a reserved keyword
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
        'RECORD_LAYOUT',
        //'RECORD_SEGMENT',
        //'SEGMENT',
        'SUB_FUNCTION',
        //'SOURCE',
        //'TP_BLOB',
        //'VS_DEF'
    );

    var $typeClasses = array();
    var $no_skip = array('SEED_KEY', 'EVENT_GROUP');

    var $keywords = array(
        'ALIGNMENT_BYTE', 'ALIGNMENT_WORD', 'ALIGNMENT_LONG', 'ADDR_EPK', 'ADDRESS_MAPPING', 'ASAP2_VERSION', 'AXIS_PTS_X', 'AXIS_PTS_Y',
        'BIT_MASK', 'BYTE_ORDER',
        'CAN_ID_FIXED', 'COMPU_METHOD', 'COMPU_TAB_REF', 'CPU_TYPE', 'CUSTOMER_NO',
        'DEPOSIT', 'DISPLAY_IDENTIFIER',
        'ECU', 'ECU_ADDRESS', 'ECU_ADDRESS_EXTENSION', 'EPK', 'EXTENDED_LIMITS',
        'FIRST_PID', 'FNC_VALUES', 'FORMAT', 'FUNCTION_LIST',
        'IF_DATA',
        'LENGTH',
        'MATRIX_DIM', 'MEMORY_LAYOUT',
        'NO_AXIS_PTS_X', 'NO_AXIS_PTS_Y', 'NUMBER',
        'PHONE_NO', 'PROJECT_NO', 'PAGE_SWITCH',
        'RASTER', 'RESERVED',
        'SYMBOL_LINK', 'SYSTEM_CONSTANT',
        'TAB_VERB',
        'USER',
        'VERSION',
    );

    var $watch_vars = array('uom', 'longDesc');

    var $end = false;
    var $outFile;
    var $tokens;
    var $silent = false;
    var $root;
    var $curr;
    var $skip;

    public static $drop_back;

    function __construct($tokens, $outFile) {
        $this->tokens = $tokens;
        //$this->root = new xmlTree($outFile);
        $this->outFile = $outFile;
        $this->root = new _BASETYPE;
        $this->curr = $this->root;
        $this->skip = true;
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, '_BASETYPE')) {
                $this->typeClasses[] = $class;
            }

        }
    }

    function __destruct() {
    }

    function Parse() {

        $f = $this->outFile . '.a2l.PARSED';
        if (file_exists($f)) {
            return;
        }
        //$this->tokens->MoveFirst();
        //Msg('Begin parse. ');

        //$this->root->AddNode('A2L', 'A2L', 'A2L');
        $data = ($this->RecursiveParse('A2L'));
        //$this->root = ($data);
        if(self::$drop_back == ''){
            $ser = gzdeflate(serialize($data));
            //file_put_contents($f, $ser);
            //return $this->getBuffer();
            return $data;
        } else {
            self::$drop_back = '';
        }
        
    }

    function RecursiveParse($type = '') {
        $silent = false;
        $skip = true;
        $bUnexpectedBeginEnd = false;
        if ($type == '') {
            $item = new A2L;
            //$curr = $this->curr;
        } else {
            $item = new $type;
        }

        if (!in_array($type, $this->mainsections)) {
            $silent = true;
            //Msg('going silent for ' . $section);
        } else {
            $silent = false;
        }

        $idx = 0; //index for Vars
        if (in_array($type, $this->no_skip)) {
            $skip = false;
        }
        $this->skip = $skip;

        while ($this->end != true) {
            $section = '';
            if(self::$drop_back != ''){
                return $item;
            }


            if (!$bUnexpectedBeginEnd) {
                $Token = $this->get($skip);
                if(in_array($Token, $this->mainsections)){
                    $ignore = array('MEASUREMENT', 'QP_BLOB');
                    if(!in_array($Token, $ignore)){
                        //missing "/begin"??? probable
                        $section = $Token;
                        $Token = '/begin';
                    }
                }
            } else {
                //Msg('Unexpected ' . $data);
                $Token = $data;
            }
            $bUnexpectedBeginEnd = false;

            switch ($Token) {
            case "false":
                break;
            case "/begin":
                if($section == ''){
                    $section = $this->get($skip);
                }

                //special handling to prevent use of reserved keyword
                if ($section == 'FUNCTION') {
                    $section = "FUNC";
                }

                if (!in_array($section, $this->typeClasses)) {
                    if (!mb_detect_encoding($section, 'ASCII', true)) {
                        $section = 'NO_NAME';
                    }
                    $string = 'class ' . $section . ' extends _BASETYPE { var $var1; }';
                    //Msg($string);
                    eval($string);
                    unset($this->typeClasses);
                    $this->typeClasses = array();
                    foreach (get_declared_classes() as $class) {
                        if (is_subclass_of($class, '_BASETYPE')) {
                            $this->typeClasses[] = $class;
                        }

                    }
                }
                //new section
                //$this->curr->add($section);

                $data = $this->RecursiveParse($section);

                if (is_array($item->$section)) {
                    if (property_exists($section, 'name')) {
                        $item->$section[$data->name] = $data;
                    } else {
                        $item->$section[] = $data;
                    }

                } else {
                    $item->$section = $data;
                }

                break;
            case "/end":
                $section = $this->get($skip);

                //special handling to prevent use of reserved keyword
                if ($section == 'FUNCTION') {
                    $section = "FUNC";
                }

                if ($section == $type) {

                    //Msg('end '.$section);
                    
                    return $item;
                    //$coll = new Collection;
                    //$this->curr->addItem($item);
                    //return $this->curr;
                } elseif (in_array($section, $this->mainsections)) {
                    Msg('Problem ending section ' . $section);
                    self::$drop_back = $section;

                } else {
                    Msg('Gone wrong ending ' . $section . '. expected ' . $type);
                    self::$drop_back = $section;
                }
                //return $item;
                break;
            default:
                if (!$silent) {

                    if (in_array($Token, $this->keywords)) {
                        $data = $this->DoKeywords($Token);
                        if (in_array($data, $this->beginend)) {
                            $bUnexpectedBeginEnd = true;
                            break;
                        }
                        if (is_string($data)) {
                            if (in_array($data, $this->keywords)) {
                                $Token = $data;
                                $data = $this->DoKeywords($Token);
                                if (in_array($data, $this->beginend)) {
                                    $bUnexpectedBeginEnd = true;
                                    break;
                                }
                            }
                        }

                        if (is_object($data)) {
                            if (is_object($item->$Token)) {
                                $item->$Token = ($data); //add object
                                $idx + count($data);
                            } else {
                                $item->$Token = $data;
                                $idx + count($data);
                            }
                        } else {
                            $item->$Token = $data;
                            $idx++;
                        }
                    } else {
                        $x = $item->countVars();
                        if ($idx < $x) {
                            $varName = $item->Var($idx);
                            if(in_array($varName, $this->mainsections)){
                                    break;
                            }
                            if (in_array($varName, $this->watch_vars)) {
                                //special handling
                                switch ($varName) {
                                case 'uom':
                                    if ($Token == 'COEFFS') {
                                        $idx++;
                                        $varName = $item->Var($idx);
                                    }
                                    break;
                                case 'longDesc':
                                    $longArr = array('CHARACTERISTIC' => array('ASCII', 'MAP', 'CURVE', 'VALUE', 'VAL_BLK'),
                                        'MEMORY_SEGMENT' => array('CODE', 'DATA', 'RESERVED', 'VARIABLES'),
                                        'COMPU_METHOD' => array('FORM', 'RAT_FUNC', 'TAB_VERB'),
                                        'MEASUREMENT' => $this->DataTypes);
                                    //echo $type.'<br/>';
                                    if (isset($longArr[$type])) {
                                        if (in_array($Token, $longArr[$type])) {
                                            $idx++;
                                            $varName = $item->Var($idx);
                                        }
                                    }
                                    break;
                                }
                            }
                            if ($Token != '') {
                                if (is_array($item->$varName)) {
                                    if (!in_array($varName, $this->mainsections)) {
                                        $item->$varName[$Token] = $Token;
                                    } else {
                                        $item->$varName[$Token] = $Token;
                                    }

                                } else {
                                    $item->$varName = $Token;
                                    $idx++;
                                }
                            }

                        }
                    }
                }
            }
        }
        return $item;
    }

    

    private function DoKeywords($keyword) {
        $ret = '';

        //Msg('   DoKeywords '.$keyword);
        switch ($keyword) {
        case 'ASAP2_VERSION':
            $ret = new ASAP2_VERSION;
            $ret->major = $this->get();
            $ret->minor = $this->get();
            break;
        case 'EXTENDED_LIMITS':
            $ret = new EXTENDED_LIMITS;
            $ret->var1 = $this->get($this->skip);
            $ret->var2 = $this->get($this->skip);
            break;
        case 'SYSTEM_CONSTANT':
            $ret = new SYSTEM_CONSTANT;
            $ret->name = $this->get($this->skip);
            $ret->constant = $this->get($this->skip);
            break;
        case 'IF_DATA':
            $ret = new IF_DATA;
            $ret->name = $this->get($this->skip);
            $ret->type = $this->get($this->skip);
            $ret->orig_addr = $this->get($this->skip);
            break;
        case 'NO_AXIS_PTS_X':
        case 'NO_AXIS_PTS_Y':
        case 'FNC_VALUES':
        case 'AXIS_PTS_X':
        case 'AXIS_PTS_Y':
            $no_axis = array('NO_AXIS_PTS_X', 'NO_AXIS_PTS_Y');
            $ret = new RecordLayoutVars;
            $vv = get_class_vars(get_class($ret));
            foreach ($vv as $k => $r) {
                if(($k == 'type') and (in_array($keyword, $no_axis))) break;
                $t = $this->get($this->skip);
                if ($t != '') {
                    if (in_array($t, $this->keywords)) {
                        //found keyword, return and resume with keyword
                        return $t;
                    } else {
                        $ret->$k = $t;
                    }
                } else {
                    //do nothing
                    break;
                }
            }
            break;
        case 'FUNCTION_LIST':
            //Msg('FUNCTION_LIST');
            $ret = $this->get($this->skip);
            break;
        case 'TAB_VERB';
            $ret = array();
            $test = $this->get(false);
                if (in_array($test, $this->keywords))
                    break;
            if($test != ''){
                $cnt = $test;
            
                for ($x = 0; $x < $cnt; $x++) {
                    $idx = $this->get($this->skip);
                    $desc = $this->get($this->skip);
                    $ret[$idx] = $desc;
                }
            } else {
                $desc = $this->get();
            }
            

        default:
            //$ret = new _BASETYPE;
            $ret = $this->get($this->skip);
        }
        return $ret;
    }

    public function get(bool $skip_empty = false) {
        $t = '';
        if ($this->tokens->endToken === true) {
            $this->end = true;
            return false;
        }

        if ($this->skip) {
            do {
                $t = trim($this->tokens->getToken(), chr(34));
                if ($t === 'false'){
                    $this->end = true;
                    break;
                }
            } while (($t === ''));
        } else {
            $t = trim($this->tokens->getToken(), chr(34));
            if ($t === 'false'){
                $this->end = true;
            }
        }

        return $t;
    }
}

?>