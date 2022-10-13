#!/usr/bin/php 
<?php

include_once 'sqlite3db.php';
include_once 'a2ldb.php';
include_once 'a2lparser.php';
include_once 'a2ltokenizer.php';
include_once 'validator.php';

$force = true;
$db_dir = './';


if($argc>1){
  $v = $argv[1];
    $pi2 = pathinfo($v);
    $dbfile = $db_dir.$pi2['filename'].".db";

    $zer = new Tokenizer($v);
    $parser = new a2lparser($zer, $dbfile, $force);
    $parser->Parse();
} else {
  echo 'USAGE: ./a2l-db.sh <a2l-input-file>'.PHP_EOL;
}

