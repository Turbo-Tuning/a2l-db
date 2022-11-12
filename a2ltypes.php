<?php

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


class _BASETYPE{
    public function __get($name) {
        return $this->$name;
    }

    public function __set($name, $value) {
        Msg('Set '.$name.' to '.$value);
        $this->$name = $value;
    }
}

class CompuMethod extends _BASETYPE{
    var $name;
    var $longDesc;
    var $conversionType;
    var $format;
    var $uom;
    var $compu_type;
    var $coeff_a;
    var $coeff_b;
    var $coeff_c;
    var $coeff_d;
    var $coeff_e;
    var $coeff_f;
}

class RecordLayoutVars extends _BASETYPE{
    var $lenth;
    var $idx;
    var $mode;
}

class RecordLayout extends _BASETYPE{
    var $name;
    var $NO_AXIS_PTS_X = RecordLayoutVars;
    var $NO_AXIS_PTS_Y = RecordLayoutVars;
    var $AXIS_PTS_X = RecordLayoutVars;
    var $AXIS_PTS_Y = RecordLayoutVars;
    var $FNC_VALUES = RecordLayoutVars;
}

class AxisDescr extends _BASETYPE{
    var $type;
    var $measurement;
    var $compu_method;
    var $conversion;
    var $max_axis_points;
    var $lowerLimit;
    var $upperLimit;
}

class Characteristic extends _BASETYPE{
    var $name;
    var $desc;
    var $type;
    var $addr;
    var $record_layout;
    var $maxDiff;
    var $compu_method;
    var $lowerLimit;
    var $upperLimit;
    var $format;
    var $axis_descriptions;
    var $function;

    public function __construct(){
        $this->axis_descriptions = new Collection;
    }
}

class Measurement extends _BASETYPE{
    var $name;
    var $desc;
    var $data_type; 
    var $compu_method;
    var $resolution;
    var $accuracy;
    var $lowerLimit;
    var $upperLimit;
    var $format;
    var $addr;
}