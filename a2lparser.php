<?php

class a2lparser{
	var $db_dir = 'parser_a2l/db/';
	var $trenner = array(" ", "[", "]", "{" ,"}" ,"(" ,")" ,"," ,";", "=" ,"\r" ,"\l" ,"/", '"');
	var $beginend = array('/begin', '/end');
	var $mainsections = array(
		'A2ML' => 0, 
		'AXIS_PTS' => 2, 
		'CHARACTERISTIC' => 2, 
		'COMPU_METHOD' => 2, 
		'COMPU_TAB' => 2, 
		'COMPU_VTAB' => 2,
		'FUNCTION' => 2, 
		'GROUP' => 0, 
		'HEADER' => 1, 
		'IF_DATA' => 1, 
		'MEASUREMENT' => 2, 
		'MODULE' => 2, 
		'MOD_COMMON' => 0, 
		'MOD_PAR' => 1, 
		'PROJECT' => 2, 
		'RECORD_LAYOUT' => 1);
	var $subsections = array(
		'CAN_PARAM', 'CHECKSUM', 'CHECKSUM_PARAM', 'DEFINED_PAGES', 'DISTAB_CFG', 'FLASH_COPY', 
		'IF_DATA', 'MEMORY_SEGMENT', 'RASTER', 'SEED_KEY', 'SOURCE', 'SUB_FUNCTION', 'TP_BLOB');
	var $keywords = array(
		'ADDR_EPK', 'ADDRESS_MAPPING', 'BYTE_ORDER', 'CPU_TYPE', 'CUSTOMER_NO', 'DEPOSIT', 'ECU', 'EPK', 'EXTENDED_LIMITS', 'FORMAT', 
		'FUNCTION_LIST', 'KP_BLOB', 'MEMORY_LAYOUT', 'PHONE_NO', 'PROJECT_NO', 'PAGE_SWITCH', 'QP_BLOB', 'SYSTEM_CONSTANT', 'USER', 'VERSION');

	var $length;
	var $tokens;

    var $tree;

	function __construct($tokens, $outFile){
		$this->tokens = $tokens;

        $this->tree = new GeneralTree($outFile);
	

	}

	function __destruct(){
	}

	function Parse(){
		set_time_limit(0);

		
		$this->tokens->MoveFirst();
		//Msg('Begin parse. ');
		
		$this->ParseSub();
		$this->tree->endDocument();
		//file_put_contents(_DATAPATH.$this->db_dir.$this->outFile, $this->tree->outputMemory(true));
				
	}

	function ParseSub($depth = 0){
		$silent = false;
		while (!$this->tokens->EOF()){
			$Token = trim($this->tokens->getNextToken(), chr(34));
			if(in_array($Token, $this->beginend)){
				$Token2 = trim($this->tokens->getNextToken(), chr(34));
				if(in_array($Token2, array_keys($this->mainsections))){
					$x = $this->mainsections[$Token2];
				}
			}
			
			switch($Token){
				case "ASAP2_VERSION":
					$Token2 = $this->get();
					$Token3 = $this->get();
					if(!$silent) $this->tree->attribute($Token, $Token2.'.'.$Token3);
					break;
				case "MEMORY_LAYOUT":
					$Token2 = $this->get();
					$Token3 = $this->get();
					$Token4 = $this->get();
					$Token5 = $this->get();
					$Token6 = $this->get();
					$Token7 = $this->get();
					$Token8 = $this->get();
					$Token9 = $this->get();
					if (!$silent) $this->tree->insert($Token, $Token2);
					if (!$silent) $this->tree->insert($Token, $Token3);
					if (!$silent) $this->tree->insert($Token, $Token4);
					if (!$silent) $this->tree->insert($Token, $Token5);
					if (!$silent) $this->tree->insert($Token, $Token6);
					if (!$silent) $this->tree->insert($Token, $Token7);
					if (!$silent) $this->tree->insert($Token, $Token8);
					if (!$silent) $this->tree->insert($Token, $Token9);
					break;
				case "SYSTEM_CONSTANT":
					$Token2 = $this->get();
					$Token3 = $this->get();
					if(!$silent) $this->tree->insert($Token, $Token2.'='.$Token3);
					break;
				case "/begin":
					if($Token2 == "A2ML"){
						//Go silent
						$silent = true;
					}
					if (!$silent){
						$this->tree->add($Token2);
						if(isset($x)){
							if($x == 1){
								$Token3 = $this->get();
								$this->tree->attribute('shortDesc', $Token3);
							} elseif($x == 2){
								$Token3 = $this->get();
								$Token4 = $this->get();
								$this->tree->attribute('shortDesc', $Token3);
								$this->tree->attribute('longDesc', $Token4);
							} elseif($x == 0){
								//Msg('Why am i here? '.$Token.' '.$Token2);
							}
						}
					} 
					
					break;
				case "/end":
					if (!$silent) $this->tree->close();

					if ($Token2 == 'A2ML') $silent = false;
					break;
				default:
					if(in_array($Token, $this->keywords)){
						$nextToken = trim($this->tokens->peekNextToken(), chr(34));
						if((!in_array($nextToken, $this->beginend)) and (!in_array($nextToken, $this->keywords)) and (strlen($nextToken) !== 0)){
							$Token2 = $this->get();
							
							//Msg($Token.' and '.$Token2);
							if (!$silent) $this->tree->insert($Token, $Token2);
						} else {
							//Msg('been here');
							if (!$silent){
								$this->tree->insert($Token, 'empty');
								$away[] = $this->tokens->getNextToken();
							} 
						}
					} else {
						if (!$silent) {
							//Msg('Lost token '.$Token);
							if($Token != '') {
								$this->tree->insert('text', $Token);
							}
						}
					}
			}
		}
		//var_dump($away);
	}

	public function Prt(){
		return $this->tree->Prt();
	}

	public function getBuffer(){
		return $this->tree->getBuffer();
	}

	public function get(){
		$p = $this->tokens->peekNextToken();
		if(!in_array($p, $this->beginend)){
			$t = trim($this->tokens->getNextToken(), chr(34));
		} else {
			$t = '';
		}
		return $t;
	}
}

?>
