<?php

//A2L tokenizer

class Tokenizer {
    var $endToken = false;
    var $firstToken = true;
    var $tokens;
    var $idx_Current; //current position in the tokenizer array
    var $total;
    var $startIdx; //where the current section started
    var $endIdx; //where the current section ends
    var $str;
    var $bEPKfound = false; // true if EPK found in the a2l file
    var $buffer;
    var $slashedbuff;

    public function Progress() {
        $p = ($this->idx_current / $this->total) * 100;
        return $p;
    }

    public function __construct($file) {
        $this->idx_current = 0;

        $str = file_get_contents($file);
        $str = str_replace(chr(42) . chr(47), chr(42) . chr(47) . chr(32), $str); // '*/'
        $str = str_replace('\"', '*', $str);
        $str = str_replace(chr(9), ' ', $str);
        $str = str_replace(chr(13) . chr(10), ' ||| ', $str);
        $str = str_replace(chr(246), 'o', $str);
        //$str = utf8_decode($str);
        $str = removeUnwanted($str);
        //$str = Normalizer::normalize($str, Normalizer::FORM_C);
        $str = str_replace(chr(10), ' ', $str);
        $this->buffer = $str;
    }

    public function getToken(){
        if($this->firstToken){
            $tok = strtok($this->buffer, ' ');
            $this->firstToken = false;
        } else {
            $tok = strtok(' ');
            if($tok === false) 
                return 'false';
        }

        
        if ($tok !== false) {
            if (substr_count($tok, '|||')) {
                //do nothing
            } elseif (substr_count($tok, '/*') > 0) {
                if (substr_count($tok, '*/') > 0) {
                    //$tok = strtok(' ');
                    //$tok = $fifo->Get();
                } else {
                    do {
                        $tok = strtok(' ');
                        if($tok === false) 
                            return 'false';
                        //$tok = $fifo->Get();
                    } while (substr_count($tok, '*/') == 0);
                }
            } elseif ((strpos($tok, '"') !== false) and ((substr_count($tok, '"') < 2) or (substr_count($tok, '"') > 2))) {
                $c = substr_count($tok, '"');
                if ((($c) % 2) === 0) {
                    if (substr($tok, 0, 3) == '"""') {
                        $rest = substr($tok, 3);
                        $tok = '"' . substr($rest, 0, strlen($rest) - 3) . '"';
                    }
                } else {
                    do {
                        $add = strtok(' ');
                        if($add === false) 
                            return 'false';
                        $add = removeUnwanted($add);

                        //$add = $fifo->Get();
                        $tok .= " " . $add;

                        $a = substr_count($add, chr(34));
                        $b = substr_count($add, chr(34) . chr(34));
                        $c = substr($add, -1);
                        $d = substr($add, -2);
                        $f = substr_count($tok, '"');
                        $g = $f % 2;
                        if ($g === 0) {
                            $a = 0;
                            //$e = $this->tokPeek($tok);
                        } else {
                            $e = '    ';
                        }

                        if ($b == 0) {
                            $a = 1;
                        }
                        if ($b == 0 and $c == '"') {
                            $a = 0;
                        }

                        //if($e == ' |||'){
                        //    $a = 0;
                        //}
                    } while (($a != 0));
                }
                $tok = $this->SpecialTokStrip($tok);
                $tok = str_replace('|||', ' - ', $tok);

                $tok = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $tok);
                return $tok;
                //Msg(count($toks).' tokens')    ;

            } else {
                $tok = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $tok);
                return $tok;
            }
            //$tok = strtok(' ');
            //$tok = $fifo->Get();
        } else {
            Msg('Tokenizer false');
            $this->endToken = true;
            return 'false';
        }
    }

    private function ReindexArray($arr) {
        $c = array_key_last($arr);
        for ($i = 0; $i < $c; $i++) {
            if (isset($arr[$i])) {
                $newarr[] = $arr[$i];
            }
        }
        return $newarr;
    }

    public function WriteTokens($file) {
        $fh = fopen($file, 'w');
        foreach ($this->tokens as $key => $val) {
            if (isset($val)) {
                fputs($fh, $key . " => " . $val . PHP_EOL);
            }
        }
        fclose($fh);
    }

    public function __destruct() {
        unset($str);
        unset($toks);
    }

    public function MoveFirst() {
        $this->idx_current = 0;
    }

    public function EOF() {
        return $this->endToken;
    }

    public function GetNextToken() {
        $token = "notoken";
        //$log = new logger;
        //$log->log("Current token:".$this->idx_current." of ".$this->total);
        if ((!$this->EOF())) {
            $token = $this->tokens[$this->idx_current];
            $this->idx_current++;
        } else {
            $token = 'notoken';
            return $token;
        }
        if (substr_count($token, chr(10)) > 0) {
            $token = str_replace(chr(10), '', $token);
        }

        //$log->log("GetNextToken:".$token);
        return $token;
    }

    public function Begin($section_name) {
        $this->startIdx = $this->idx_current;

        //echo "$this->idx_current/$this->total $section_name<br/>";

        //find the end of the section
        $this->endIdx = 0;
        switch ($section_name) {
        case "CHARACTERISTIC":
        case "HEADER":
        case "MEASUREMENT":
        case "COMPU_METHOD":
        case "COMPU_VTAB":
        case "RECORD_LAYOUT":
        case "AXIS_DESCR":
        case "FUNCTION":
            $i = $this->idx_current + 1;
            $c = '';
            do {
                $i++;
                $t = $this->PeekAbsToken($i);
                if ($t == "/end") {
                    $c = $this->PeekAbsToken($i + 1);
                }
            } while (($section_name != $c) and ($i < $this->total - 1));
            $this->endIdx = $i + 1;
        }
    }

    public function GotoSectionEnd() {
        $this->idx_current = $this->endIdx;
    }

    public function GetNextNonEmpty($var = '') {
        $t = '';
        if ($this->idx_current < $this->total - 1) {
            if (($this->PeekNextToken() == '|||')) {
                if ($this->PeekNextToken(1) == '|||') {
                    $t = $this->GetNextToken();
                    return '""';
                }
            }
            //$t = $this->GetNextToken();
            do {
                $t = $this->GetNextToken();
            } while (($t == '|||'));
            return $t;
        } else {
            return '';
        }
    }

    public function FieldExist($name) {
        $b = false;
        for ($n = $this->startIdx; $n < $this->endIdx; $n++) {
            if ($this->PeekAbsToken($n) == $name) {
                $b = true;
            }

        }
        return $b;
    }

    public function GetNamedToken($name, $idx, $block = '') {
        do {
            $t = $this->PeekAbsToken($idx);
            $idx++;
        } while (($t != $name) and ($idx < $this->endIdx));
        if ($idx != $this->endIdx) {
            $t = $this->PeekAbsToken($idx);
            $this->idx_current = $idx;
            return $t;
        } else {
            return '';
        }

    }

    public function PeekAbsToken($idx) {
        return $this->tokens[$idx];
    }

    public function PeekCurrToken() {
        $peek = $this->tokens[$this->idx_current - 1];
        return $peek;
    }

    public function PeekNextToken($idx = 0) {
        if (($this->idx_current + $idx) <= $this->total) {
            $peek = $this->tokens[$this->idx_current + $idx];
            if (substr_count($peek, chr(10)) > 0) {
                $peek = str_replace(chr(10), '', $peek);
            }
            if (($this->idx_current + $idx) > $this->total) {
                //Msg("Peeking ".$peek.' '.bin2hex($peek));
            }

            return $peek;
        } else {
            //throw new Exception('Index greater than array');
            //$log = new logger;
            //Msg("Exception: index greater than array");
            //Msg('Peeking: '.$idx);
            //Msg('Current: '.$this->idx_current.' of '.$this->total);
            //Msg('Last 5 tokens:');
            for ($t = $this->idx_current - 5; $t < 5; $t++) {
                //Msg($this->tokens[$t]);
            }
            //Msg('Moving on');

            return '';
        }

    }

    public function GetCurrToken() {
        return $this->tokens[$this->idx_current];
    }

    private function GetStrFromTok($buff, $separator) {
        $str = strtok($buff, $separator);
        if (substr_count($str, chr(10)) > 0) {
            $str = str_replace(chr(10), '', $str);
        }
        return $str;
    }

    private function SpecialTokStrip($tok) {
        $tok = substr($tok, 1);
        if (substr_count($tok, chr(34) . chr(34)) > 0) {
            $tok = str_replace(chr(34) . chr(34), "*", $tok);
            if (substr($tok, -2) == '*"') {
                $tok = str_replace('*"', '*', $tok);
            }
            if (substr($tok, -2) == '."') {
                $tok = str_replace('."', '.', $tok);
            }

        }
        if (substr($tok, -1) == '"') {
            $tok = (substr($tok, -0, -1));
        }
        return $tok;
    }

    

    function tokPeek($szPos) {
        $slashed = addslashes($szPos);
        $pos = strpos($this->buffer, $szPos);

        $szLen = strlen($szPos);
        $test = substr($this->buffer, $pos + $szLen, 4);
        return $test;
    }
}

?>