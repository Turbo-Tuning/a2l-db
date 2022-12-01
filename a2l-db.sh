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