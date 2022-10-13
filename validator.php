<?php

class validator{

    public function validate($arr){
        $result = true;
        return $result;
        //Apply some basic data validation rules
        if($arr['name'] == ''){
            $result = false;
        }
        if(isset($arr['format'])){
            if(substr($arr['format'],1,1) != '%'){
                $result = false;
            }
        }
        return $result;
    }
}

?>