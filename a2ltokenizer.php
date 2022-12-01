<?php
/**
 * This file is part of TurboTuner/a2l-db package.
 *
 * Licensed under the GPL3 License
 * (c) TurboTuner
 */

//A2L tokenizer

class Tokenizer {
    var $endToken = false;
    var $firstToken = true;
    var $buffer;

    public function __construct($file) {

        $str = file_get_contents($file);
        $str = str_replace(chr(42) . chr(47), chr(42) . chr(47) . chr(32), $str); // '*/'
        $str = str_replace('\"', '*', $str);
        $str = str_replace(chr(9), ' ', $str);
        $str = str_replace(chr(13) . chr(10), ' ||| ', $str);
        $str = str_replace(chr(246), 'o', $str);
        $str = utf8_encode($str);
        $str = Normalizer::normalize($str, Normalizer::FORM_C);
        $str = str_replace(chr(10), ' ', $str);
        $this->buffer = $str;
    }

    public function getToken(){
        if($this->firstToken){
            $tok = strtok($this->buffer, ' ');
            $this->firstToken = false;
        } else {
            $tok = strtok(' ');
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
                            return false;
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
                            return false;

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
            } else {
                $tok = preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $tok);
                return $tok;
            }
        } else {
            $this->endToken = true;
            return false;
        }
    }

    public function __destruct() {
        unset($str);
        unset($toks);
    }

    public function EOF() {
        return $this->endToken;
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
}

?>