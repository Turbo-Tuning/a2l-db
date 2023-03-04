#!/usr/bin/php 
<?php
/**
 * This file is part of TurboTuner/a2l-db package.
 *
 * Licensed under the GPL3 License
 * (c) TurboTuner
 */

include_once 'Collection.php';
include_once 'a2lParser.php';
include_once 'a2ltokenizer.php';
include_once 'a2ltypes.php';

$force = true;
$db_dir = './db/';


if($argc>1){
  $ttime_start = microtime(true);
  $time_start = $ttime_start;

  $v = $argv[1];
    $pi2 = pathinfo($v);
    $dbfile = $db_dir.$pi2['filename'].'.parsed';

    $zer = new Tokenizer($v);
    $time_end = microtime(true);
		$execution_time = ($time_end - $time_start);
    echo 'Tokenizer Time: '.round($execution_time, 2).' Secs'.PHP_EOL;

    $parser = new a2lparser($zer, $dbfile);
    $buff = $parser->Parse();
    file_put_contents($dbfile, gzdeflate(serialize($buff)));

    $ttime_end = microtime(true);
    $texec_time = ($ttime_end - $ttime_start);
    echo 'Total processing time: '.round($texec_time, 2).' Secs'.PHP_EOL.PHP_EOL;
} else {
  echo 'USAGE: ./a2l-db.sh <a2l-input-file>'.PHP_EOL;
}

function Msg($text){
  echo $text;
}

function removeUnwanted($str) {
    $unwanted_array = array('Š' => 'S', 'š' => 's', 'Ž' => 'Z', 'ž' => 'z', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A', 'Ç' => 'C', 'È' => 'E', 'É' => 'E',
        'Ê' => 'E', 'Ë' => 'E', 'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O', 'Ù' => 'U',
        'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a', 'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o', 'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
        'ö' => 'o', 'ø' => 'o', 'ü' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y');
    $str = strtr($str, $unwanted_array);
    return $str;
}