<?php
declare(strict_types=1);

define ('UBYTE', 1); //1 byte unsigned integer
define ('SBYTE', 1); //1 byte signed integer
define ('UWORD', 2); //2 byte unsigned integer
define ('SWORD', 2); //2 byte signed integer
define ('ULONG', 4); //4 byte unsigned integer
define ('SLONG', 4); //4 byte signed integer
define ('FLOAT32_IEEE', 32); //32 bit floating point IEEE format
define ('FLOAT64_IEEE', 64); //64 bit floating point IEEE format
define ('FLOAT32_TASKING', 32); //32 bit floating point tasking format

function getByteSize($type){
    if($type == ''){
        return 2;
    }
    return constant($type);
}


class _BASETYPE implements Countable{


    public function __get($name) {
        //Msg('Get '.$name.' with value= '.$this->name);
        if(!isset($this->$name)){
            return bin2hex(random_bytes(5));
        }
        return $this->$name;
    }

    public function __set($name, $value) {
        //Msg('Set '.$name.' to '.$value.' in '.get_class($this));
        $this->$name = $value;
    }

    public function Var($i){
        return array_keys($this->Vars())[$i];
    }

    public function Vars(){
        return get_object_vars($this);
    }

    public function count(){
        return count($this->Vars());
    }

    public function countVars(){
        return count($this->Vars());
    }


}

class ASAP2_VERSION extends _BASETYPE{
    var $major;
    var $minor;
}

class AXIS_PTS extends _BASETYPE{
    var $var1;
}

class A2L extends _BASETYPE{
    var $ASAP2_VERSION;
    var $name;
    var $PROJECT;
    var $IF_DATA;
    var $MODULE;
}

class CHECKSUM extends _BASETYPE{
    var $var1;
}

class CAN_PARAM extends _BASETYPE{
    var $var1;
}

class CHECKSUM_PARAM extends _BASETYPE{
    var $var1;
}

class DEFINED_PAGES extends _BASETYPE{
    var $var1;
}

class DIAG_BAUD extends _BASETYPE{
    var $var1;
}

class DISTAB_CFG extends _BASETYPE{
    var $type;
    var $dataType;
    var $byteOrder;
    var $triggerSegmentAddr;
    var $triggerConfiguration;
    var $TRG_MOD;
}

class PROJECT extends _BASETYPE{
    var $name;
    var $longDesc;
    var $HEADER;
    var $MODULE;
}

class FLASH_COPY extends _BASETYPE{
    var $var1;
}

CLASS HEADER extends _BASETYPE{
    var $name;
    var $VERSION;
    var $PROJECT_NO;
}

CLASS MODULE extends _BASETYPE{
    var $name;
    var $longDesc;
    var $DEPOSIT;
    var $FORMAT;
    var $A2ML;
    var $MOD_PAR;
    var $MOD_COMMON;
    var $IF_DATA;
    var $CHARACTERISTIC = array();
    var $MEASUREMENT = array();
    var $FUNC = array();
    var $COMPU_METHOD = array();
    var $RECORD_LAYOUT = array();
    var $COMPU_VTAB = array();
}

class PAGE_SWITCH extends _BASETYPE{
    var $var1;
}

class SEED_KEY extends _BASETYPE{
    var $var1;
}

class SEGMENT extends _BASETYPE{
    var $name;
    var $no_of_pages;
    var $address_extension;
    var $compression_method;
    var $encryption_method;
}

CLASS A2ML extends _BASETYPE{
    var $name;
    var $longDesc;
    var $IF_DATA;
}

CLASS MOD_PAR extends _BASETYPE{
    var $name;
    var $VERSION;
    var $ADDR_EPK;
    var $EPK;
    var $CUSTOMER_NO;
    var $USER;
    var $PHONE_NO;
    var $CPU_TYPE;
    var $ECU;
    var $MEMORY_SEGMENT = array();
    var $MEMORY_LAYOUT = array();
    var $SYSTEM_CONSTANT = array();
    var $IF_DATA = array();
    var $CALIBRATION_METHOD = array();
}

class SYSTEM_CONSTANT extends _BASETYPE{
    var $name;
    var $constant;
}

class ETK_XETK_ACCESS extends _BASETYPE{
    var $name;
}

class CALIBRATION_METHOD extends _BASETYPE{
    var $name;
    var $version;
    var $CALIBRATION_HANDLE;
}

class CALIBRATION_HANDLE extends _BASETYPE{
    var $name;
}

class MEMORY_LAYOUT extends _BASETYPE{
    var $name;
    var $location;
    var $orig_addr;
    var $dummy1;
    var $dummy2;
    var $dummy3;
    var $dummy4;
    var $dummy5;
    var $IF_DATA = array();
}

class MEMORY_SEGMENT extends _BASETYPE{
    var $name;
    var $longDesc;
    var $type;
    var $storage;
    var $location;
    var $orig_addr;
    var $length;
    var $IF_DATA = array();
    var $segments = array();

    public function __construct(){
        
    }
}


//for use with MEMORY_LAYOUT and MEMORY_SEGMENT
class IF_DATA extends _BASETYPE{
    var $name;
    var $type;
    var $orig_addr;
    var $mapping_addr;
    var $length;
    var $TP_BLOB;
    var $QP_BLOB;
    var $SOURCE;
    var $RASTER;
    var $SEGMENT;
}

class LOC_MEASUREMENT extends _BASETYPE{
    var $var1;
}

class MOD_COMMON extends _BASETYPE{
    var $name;
    var $BYTE_ORDER;
    var $ALIGNMENT_BYTE;
    var $ALIGNMENT_WORD;
    var $ALIGNMENT_LONG;
}

class COMPU_METHOD extends _BASETYPE{
    var $name;
    var $longDesc;
    var $conversionType;
    var $FORMAT;
    var $uom;
    var $COMPU_TYPE;
    var $coeff_a;
    var $coeff_b;
    var $coeff_c;
    var $coeff_d;
    var $coeff_e;
    var $coeff_f;
}

class COMPU_TAB extends _BASETYPE{
    var $var1;
}

class COMPU_VTAB extends _BASETYPE{
    var $name;
    var $longDesc;
    var $type;
    var $qty;
    var $enum = array();
}

class RecordLayoutVars extends _BASETYPE{
    
    var $idx;
    var $length;
    var $type;
    var $mode;
}

class RECORD_LAYOUT extends _BASETYPE{
    var $name;
    var $NO_AXIS_PTS_X;
    var $NO_AXIS_PTS_Y;
    var $AXIS_PTS_X;
    var $AXIS_PTS_Y;
    var $FNC_VALUES;
    var $RESERVED;
}

class REF_CHARACTERISTIC extends _BASETYPE{
    var $var1;
}

class IN_MEASUREMENT extends _BASETYPE{
    var $var1;
}

class OUT_MEASUREMENT extends _BASETYPE{
    var $var1;
}

class AXIS_DESCR extends _BASETYPE{
    var $type;
    var $measurement;
    var $compu_method;
    //var $conversion;
    var $max_axis_points = 0;
    var $lowerLimit;
    var $upperLimit;
    var $EXTENDED_LIMITS;
}

class CHARACTERISTIC extends _BASETYPE{
    var $name;
    var $longDesc;
    var $type;
    var $addr;
    var $record_layout;
    var $maxDiff;
    var $COMPU_METHOD;
    var $lowerLimit;
    var $upperLimit;
    var $FORMAT;
    var $DEPOSIT;
    var $AXIS_DESCR = array();
    var $function;
    var $IF_DATA = array(); 
    var $FUNCTION_LIST;
    var $EXTENDED_LIMITS;
    

    public function __construct(){
        
    }
}

class MEASUREMENT extends _BASETYPE{
    var $name;
    var $longDesc;
    var $data_type; 
    var $compu_method;
    var $resolution;
    var $accuracy;
    var $lowerLimit;
    var $upperLimit;
    var $FORMAT;
    var $ECU_ADDRESS;
    var $IF_DATA;
    var $FUNCTION_LIST;
}

class SOURCE extends _BASETYPE{
    var $name;
    var $var1;
    var $var2;
    var $QP_BLOB = array();
}

class FUNC extends _BASETYPE{
    var $name;
    var $longDesc;
}

class SUB_FUNCTION extends _BASETYPE{
    var $func = array();
}

class DEF_CHARACTERISTIC extends _BASETYPE{
    var $characteristic = array();
}

CLASS KP_BLOB extends _BASETYPE{
    var $var1;
}

CLASS TP_BLOB extends _BASETYPE{
    var $name;
    var $longDesc;
}

CLASS QP_BLOB extends _BASETYPE{
    var $name;
    var $LENGTH;
    var $CAN_ID_FIXED;
    var $FIRST_PID;
    var $RASTER;
}

CLASS RASTER extends _BASETYPE{
    var $name;
    var $longDesc;
}

CLASS EXTENDED_LIMITS extends _BASETYPE{
    var $var1;
    var $var2;
}

class FUNCTION_LIST extends _BASETYPE{

}

class APPL_PROT extends _BASETYPE{
    var $var1;
}

class MCMESS extends _BASETYPE{
    var $var1;
}

class VS_DEF extends _BASETYPE{
    var $var1;
}

class PSEUDO_ADR extends _BASETYPE{
    var $var1;
}

class COLDSTART_HANDSHAKE extends _BASETYPE{
    var $var1;
}

class SESSION extends _BASETYPE{
    var $var1;
}

class TIME_DEF extends _BASETYPE{
    var $var1;
}

class FLASH extends _BASETYPE{
    var $var1;
}

class COPY extends _BASETYPE{
    var $var1;
}

class CAN extends _BASETYPE{
    var $var1;
}

class ADDRESS extends _BASETYPE{
    var $var1;
}

class K_LINE extends _BASETYPE{
    var $var1;
}

class ETK_PRESENCE_CHECK extends _BASETYPE{
    var $var1;
}

class UNIT extends _BASETYPE{
    var $var1;
}

class ANNOTATION extends _BASETYPE{
    var $var1;
}

class TransportProtocolVersion extends _BASETYPE{
    var $var1;
}