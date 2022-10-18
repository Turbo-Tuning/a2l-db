#!/usr/bin/php 
<?php

include_once 'a2lparser.php';
include_once 'a2ltokenizer.php';
include_once 'gtree.php';
include_once 'xmlQuery.php';

$force = true;
$db_dir = './';


if($argc>1){
  $v = $argv[1];
    $pi2 = pathinfo($v);
    $dbfile = $db_dir.$pi2['filename'].".xml";

    $zer = new Tokenizer($v);
    $parser = new a2lparser($zer, $dbfile);
    $parser->Parse();

    $query = new xmlQuery($dbfile);
    $ret = $query->getListOfCharacteristics();
    var_dump($ret);
    
} else {
  echo 'USAGE: ./a2l-db.sh <a2l-input-file>'.PHP_EOL;
}

