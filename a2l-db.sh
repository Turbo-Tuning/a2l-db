#!/usr/bin/php 
<?php

include_once 'Collection.php';
include_once 'a2lparser.php';
include_once 'a2ltokenizer.php';
include_once 'gtree.php';
include_once 'xmlQuery.php';
include_once 'a2ltypes.php';
include_once 'a2lArrayManager.php';

$force = true;
$db_dir = './';


if($argc>1){
  $ttime_start = microtime(true);
  $time_start = $ttime_start;

  $v = $argv[1];
    $pi2 = pathinfo($v);
    $dbfile = $db_dir.$pi2['filename'];

    $zer = new Tokenizer($v);
    $parser = new a2lparser($zer, $dbfile);
    $parser->Parse();
    $buff = $parser->getBuffer();

    $time_end = microtime(true);
		$execution_time = ($time_end - $time_start);
    echo 'Tokenizer Time: '.round($execution_time, 2).' Secs'.PHP_EOL;

    $query = new a2lArrayManager($buff);
    $ttime_end = microtime(true);
    $texec_time = ($ttime_end - $ttime_start);
    echo 'Total processing time: '.round($texec_time, 2).' Secs'.PHP_EOL.PHP_EOL;
    $ret = $query->getCodeOffset();
    echo ('Code offset is: '.$ret.PHP_EOL);
    
} else {
  echo 'USAGE: ./a2l-db.sh <a2l-input-file>'.PHP_EOL;
}

