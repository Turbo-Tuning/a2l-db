<?php

class a2lparser{
	var $trenner = array(" ", "[", "]", "{" ,"}" ,"(" ,")" ,"," ,";", "=" ,"\r" ,"\l" ,"/", '"');

	var $length;
	var $inputBuf;
    var $force = false;
	var $infile = "";
	var $outfile = "";
	var $db;
	var $dbfile;
	var $validator;
	var $finishedDB = false;

	function __construct($inputArr, $output_db, $force=true){
		$this->inputBuf = $inputArr;
		$this->dbfile = $output_db;
		$this->db = new a2ldb($output_db, $force);
		$this->validator = new validator;
		$this->finishedDB = false;
		//$this->outfile = $outfile;
	}

	function __destruct(){
		unset($this->inputBuf);
		unset($this->db);
		if(!$this->finishedDB){
			unlink($dbfile);
		}
	}

	function Parse(){
		$pos = 0;
		$b = false;
		$last = -1;

		set_time_limit(0);

		$this->inputBuf->MoveFirst();
		
		while (!$this->inputBuf->EOF()){
			$Token = $this->inputBuf->getNextToken();
			
			if (substr_count($Token, "/begin")>0){
				$Token2 = $this->inputBuf->getNextToken();
				$this->inputBuf->Begin($Token2);
				switch ($Token2){
					case "HEADER":
						try {
							$pos = $this->ProcessBlock($Token2);
						} catch (Exception $e){
							var_dump($e);
							
						}
						break;
					case "COMPU_VTAB":
						try {
							$pos = $this->CompuVtab($pos);
						} catch (exception $e) {
							var_dump($e);
							
						}
						break;
					case "COMPU_METHOD":
						//$b = true;
						try {
							$pos = $this->CompuMethod($pos);
						} catch (Exception $e){
							echo "<pre>";
							print_r($e);
							echo "</pre>";
							var_dump($e);
							
						}
						
						break;
					case "MOD_PAR":
					case "MOD_COMMON":
						try{
							$pos = $this->ProcessBlock($Token2);
						} catch (Exception $e){
							var_dump($e);
							
						}
						
						//$b = true;
						break;
						case "FUNCTION":
							//$b = true;
							try {
								$pos = $this->Function($pos);
							} catch (Exception $e){
								var_dump($e);
								
							}
							break;
					case "MEASUREMENT":
						//$b = true;
						try {
							$pos = $this->Measurement($pos);
						} catch (Exception $e){
							var_dump($e);
							
						}
						break;
					case "CHARACTERISTIC":
						//$b = true;
						try {
							$this->Characteristic();
						} catch (Exception $e){
							var_dump($e);
							
						}
						break;
					case "RECORD_LAYOUT":
						//$b = true;
						try {
							$pos = $this->Record_Layout($pos);
						} catch (exception $e){
							var_dump($e);
							
						}
						break;
					case "AXIS_DESCR":
						try {
							$this->AxisDescr();
						} catch (Exception $e){
							var_dump($e);
							
						}
				}
				//echo " ".$pos." ";
			}
		}
		$this->UpdateDB();
	}

	private function IsValidToken($tok){
		if ($tok == 0){
			return false;
		}
		return true;
	}

	private function Function(){
		$name = $this->GetName("FUNCTION");
		$longDesc = $this->inputBuf->GetNextNonEmpty();
		$vals = array(	'name' => $name,
						'longDesc' => $longDesc);
		$this->db->Insert('functions', $vals);
		$b = false;
		do{
			$begin = $this->inputBuf->GetNextToken();
			if ($begin == "/begin"){
				$def_char = $this->inputBuf->GetNextToken();
				if (($def_char == "DEF_CHARACTERISTIC") or ($def_char == "REF_CHARACTERISTIC")){
					$b = true;
				}
			}
		} while (($b == false) and (!$this->inputBuf->EOF()));
		//$dummy = $this->inputBuf->GetNextNonEmpty();
		//$dummy = $this->inputBuf->GetNextNonEmpty();
		do{
			$name_characteristic = $this->inputBuf->GetNextNonEmpty();
			$vals = array(	'function' => $name,
							'characteristic' => $name_characteristic);
			$this->db->Insert(strtolower($def_char).'s', $vals);
		} while (($this->inputBuf->PeekNextToken(1) != $def_char) and ($this->inputBuf->PeekNextToken(1) != false));
	}

	private function CompuVtab($pos){
		//$dummy = $this->inputBuf->GetNextNonEmpty();
		//$name = $this->inputBuf->GetNextNonEmpty();
		$name = $this->GetName("COMPU_VTAB");
		$longDesc = $this->inputBuf->GetNextNonEmpty();
		$conversionType = $this->inputBuf->GetNextNonEmpty();
		$numberValuePairs = $this->inputBuf->GetNextNonEmpty();
		$vals = array('name' => $name,
						'longDesc' => $longDesc,
						'conversionType' => $conversionType,
						'numberValuePairs' => $numberValuePairs);
		
		if($this->validator->validate($vals)){
			$this->db->Insert('compu_vtabs', $vals);
		}
		$this->CloseBlock("COMPU_VTAB");
	}

	private function Record_Layout(){
		
		$axis_pts_x = "";
		$axis_pts_y = "";
		$fnc_values = "";
		//$dummy = $this->inputBuf->GetNextNonEmpty();
		//$name = $this->inputBuf->GetNextNonEmpty();
		$name = $this->GetName("RECORD_LAYOUT");
		if($this->inputBuf->FieldExist("AXIS_PTS_X")){
			$axis_pts_x = $this->inputBuf->getNamedToken("AXIS_PTS_X", $this->inputBuf->idx_current-2);
		}
		if($this->inputBuf->FieldExist("AXIS_PTS_Y")){
			$axis_pts_y = $this->inputBuf->getNamedToken("AXIS_PTS_Y", $this->inputBuf->idx_current-2);
		}
		if($this->inputBuf->FieldExist("FNC_VALUES")){
			$fnc_values = $this->inputBuf->getNamedToken("FNC_VALUES", $this->inputBuf->idx_current-2);
		}
		$vals = array(	"name" => $name,
						"axis_pts_x" => $axis_pts_x,
						"axis_pts_y" => $axis_pts_y,
						"fnc_values" => $fnc_values);
		if($this->validator->validate($vals)){
			$this->db->Insert('record_layouts', $vals);
		}
		$this->CloseBlock("RECORD_LAYOUT");
	}

	private function Measurement($pos){
		$startPos = $pos;
		$format = '';
		//$dummy = $this->inputBuf->GetNextNonEmpty();
		//$name = $this->inputBuf->GetNextNonEmpty();
		$name = $this->GetName('MEASUREMENT');
		$longDesc = $this->inputBuf->GetNextNonEmpty();
		$dataType = $this->inputBuf->GetNextNonEmpty();
		$compu_method = $this->inputBuf->GetNextNonEmpty();
		$resolution = $this->inputBuf->GetNextNonEmpty();
		$accuracy = $this->inputBuf->GetNextNonEmpty();
		$lowerLimit = $this->inputBuf->GetNextNonEmpty();
		$upperLimit = $this->inputBuf->GetNextNonEmpty();

		if ($this->inputBuf->FieldExist("FORMAT")){
			$format = $this->inputBuf->getNamedToken("FORMAT", $this->inputBuf->idx_current-2);
		}
		$ecu_address = $this->inputBuf->getNamedToken("ECU_ADDRESS", $this->inputBuf->idx_current-2);
		$vals = array(	"name" => $name,
						"longDesc" => $longDesc,
						"dataType" => $dataType,
						"compu_method" => $compu_method,
						"resolution" => $resolution,
						"accuracy" => $accuracy,
						"lowerLimit" => $lowerLimit,
						"upperLimit" => $upperLimit,
						"format" => $format,
						"addr" => $ecu_address);
		if($this->validator->validate($vals)){
			$this->db->Insert('measurements', $vals);
		}
		$this->CloseBlock("MEASUREMENT");
	}

	private function CompuMethod(){
		$co1 = '';
		$co2 = '';
		$co3 = '';
		$co4 = '';
		$co5 = '';
		$co6 = '';
		//$dummy = $this->inputBuf->GetNextNonEmpty();
		//$name = $this->inputBuf->GetNextNonEmpty();
		$name = $this->GetName("COMPU_METHOD");
		$longDesc = $this->inputBuf->GetNextNonEmpty('longDesc');
		$conversionType = $this->inputBuf->GetNextNonEmpty('conversionType');
		$format = $this->inputBuf->GetNextNonEmpty('format');
		$uom = $this->inputBuf->GetNextNonEmpty('uom');
		$dummy = $this->inputBuf->GetNextNonEmpty('dummy');
		$compu_type = $this->inputBuf->GetNextNonEmpty('compu_type');
        if ($compu_type == "COEFFS") {
				$co1 = $this->inputBuf->GetNextNonEmpty();
				$co2 = $this->inputBuf->GetNextNonEmpty();
				$co3 = $this->inputBuf->GetNextNonEmpty();
				$co4 = $this->inputBuf->GetNextNonEmpty();
				$co5 = $this->inputBuf->GetNextNonEmpty();
				$co6 = $this->inputBuf->GetNextNonEmpty();
        }
		$vals = array("name" => $name,
						"longDesc" => $longDesc,
						"conversionType" => $conversionType,
						"format" => $format,
						"uom" => $uom,
						"compu_type" => $compu_type,
						"coeff_a" => $co1,
						"coeff_b" => $co2,
						"coeff_c" => $co3,
						"coeff_d" => $co4,
						"coeff_e" => $co5,
						"coeff_f" => $co6);
		if($this->validator->validate($vals)){
			$this->db->Insert_CompuMethod($vals);
		}
		$this->CloseBlock("COMPU_METHOD");
	} 

	private function Characteristic(){
		$name = $this->GetName("CHARACTERISTIC");
		$longDesc = $this->inputBuf->getNextNonEmpty();
		$type = $this->inputBuf->getNextNonEmpty();
		$addr = $this->inputBuf->getNextNonEmpty();
		$record_layout = $this->inputBuf->getNextNonEmpty();
		$maxDiff = $this->inputBuf->getNextNonEmpty();
		$compu_method = $this->inputBuf->getNextNonEmpty();
		$lowerLimit = $this->inputBuf->getNextNonEmpty();
		$upperLimit = $this->inputBuf->getNextNonEmpty();
		//$fmt = $this->inputBuf->getNextNonEmpty();
		$format = $this->inputBuf->getNamedToken("FORMAT", $this->inputBuf->idx_current-2);
		$vals = array(	"name" => $name,
						"longDesc" => $longDesc,
						"type" => $type,
						"addr" => $addr,
						"record_layout" => $record_layout,
						"maxDiff" => $maxDiff,
						"compu_method" => $compu_method,
						"lowerLimit" => $lowerLimit,
						"upperLimit" => $upperLimit,
						"format" => $format);
		if($this->validator->validate($vals)){
			$this->db->Insert_Characteristic($vals);
		}
		$this->CloseBlock("CHARACTERISTIC");
	}

	private function AxisDescr(){
		$name = $this->GetName("AXIS_DESCR");
		$dummy = $this->inputBuf->getNextNonEmpty();
		$compu_method = $this->inputBuf->getNextNonEmpty();
		$conversion = $this->inputBuf->getNextNonEmpty();
		$max_axis_points = $this->inputBuf->getNextNonEmpty();
		$lowerLimit = $this->inputBuf->getNextNonEmpty();
		$upperLimit = $this->inputBuf->getNextNonEmpty();
	}

	private function GetName($cat){
		$name = '';
		$dummy = $this->inputBuf->getNextNonEmpty();
        if (($dummy == $cat) or ($dummy == '""')) {
            $name = $this->inputBuf->getNextNonEmpty();
        } else {
			$name = $dummy;
		}
		return $name;
	}

	private function CloseBlock($blk){
		$this->inputBuf->GotoSectionEnd();
	}

	private function getNamedToken($pos, $Token, $idx = 1, $block){
		do{ 
			list($pos, $tt3, $bb3) = $this->inputBuf->getNextToken($pos);
			if($bb3 == '/end'){
				$bb4 = $this->inputBuf->getNextToken($pos);
				if(strtoupper($bb4) == strtoupper($block)){
					return array($pos, $tt3, '');
				}
			}
		} while(($bb3 != $Token));
		for($n=0; $n<$idx; $n++){
			list($pos, $tt3, $bb3) = $this->inputBuf->getNextToken($pos);
		}
		return array($pos, $tt3, $bb3);
	}

	private function ProcessBlock($blkname){
		if (($blkname == "MOD_COMMON")){
			do {
				$Tok = $this->inputBuf->getNextToken();
				if ($Tok == "BYTE_ORDER"){
					$Tok2 = $this->inputBuf->getNextToken();
					$this->header_arr[] = array("byte_order" => $Tok2);
				}
			} while ($this->EndBlock($Tok, "MOD_COMMON"));
		} elseif (($blkname == "MOD_PAR") or ($blkname == "HEADER")){
			do{
				$comment = $this->inputBuf->getNextToken();
				if($comment == '|||') $comment = $this->inputBuf->getNextToken();
				switch ($comment){
					case "ADDR_EPK":
					case "EPK":
					case "ECU":
					case "CPU_TYPE":
					case "VERSION":
					case "PROJECT_NO":
						$comment2 = $this->inputBuf->getNextToken();
						$this->header_arr[] = array($comment => $comment2);
				}

			} while ($this->EndBlock($comment, $blkname));
		} 
	}

	private function EndBlock($tt, $blk){
		if (substr_count($tt, "/end")>0){
			$comment2 = $this->inputBuf->PeekNextToken();
			if ($comment2 == $blk){
				return false;
			}
		}
		return true;
	}

	private function UpdateDB(){
		$arr = array(	'name' => $this->g('PROJECT_NO'),
						'longDesc' => $this->g('VERSION'),
						'addr_epk' => $this->g('ADDR_EPK'),
						'byte_order' => $this->g('byte_order'),
						'ecu' => $this->g('ECU'),
						'epk' => $this->g('EPK'),
						'a2l_filename' => '',
						'a2l_filesize' => strlen($this->length));
 		$this->db->Insert('header_info', $arr);

		$this->finishedDB = true;
	}

	private function g($name){
		$a = $this->header_arr;
		foreach($a as $key => $val){
			foreach($val as $key2 => $val2)
			if($key2 == $name){
				return '"'.$val2.'"';
			}
		}
		return '""';
	}

	private function special_trim($in){
		$in = str_replace(chr(9), '', $in);
		$in = str_replace('&#9;', '', $in);
		$in = str_replace(chr(10), '', $in);
		return $in;
	}
}

?>