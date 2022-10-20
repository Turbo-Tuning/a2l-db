#!/usr/bin/php 
<?php

include_once 'a2lparser.php';
include_once 'a2ltokenizer.php';
include_once 'gtree.php';
include_once 'xmlQuery.php';

$force = true;
$db_dir = './';


if($argc>1){
  $time_start = microtime(true);

  $v = $argv[1];
    $pi2 = pathinfo($v);
    $dbfile = $db_dir.$pi2['filename'];

    $zer = new Tokenizer($v);
    $parser = new a2lparser($zer, $dbfile);
    $parser->Parse();
    $parser->Prt();

    $time_end = microtime(true);
		$execution_time = ($time_end - $time_start);
    echo 'Total Execution Time: '.round($execution_time, 2).' Secs'.PHP_EOL;

    $query = new xmlQuery($dbfile.'.xml.zip');
    $ret = $query->getListOfCharacteristics();
    //var_dump($ret);
    
} else {
  echo 'USAGE: ./a2l-db.sh <a2l-input-file>'.PHP_EOL;
}

