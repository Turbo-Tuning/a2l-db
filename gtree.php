<?php

/*$w=new XMLWriter();
$w->openMemory();
$w->startDocument('1.0','UTF-8');
$w->startElement("root");
    $w->writeAttribute("ah", "OK");
    $w->text('Wow, it works!');
$w->endElement();*/

class GeneralTree{

    var $data;
    var $outFile;

    public function __construct($name){
        $this->outFile = $name;
        $this->data = new XMLWriter();
        $this->data->openMemory();
        $this->data->setIndent(true);
        $this->data->startDocument('1.0','UTF-8');
        $this->data->startElement("A2L");
    }

    public function attribute($title, $val){
        $this->data->writeAttribute($title, $val);
    }

    public function add($data){
        $this->data->startElement($data);
    }

    public function insert($data, $text = ''){
        $this->data->writeElement($data, $text);
    }

    public function text($data){
        $this->data->text(' '.$data);
    }

    public function close(){
        $this->data->endElement();
    }

    public function endDocument(){
        $this->data->endDocument();
    }

    public function getBuffer(){
        $buff = utf8_encode(($this->data->outputMemory(true)));
        return $buff;
    }

    public function Prt(){
        $buff = utf8_encode(($this->data->outputMemory(true)));
        $compressed = gzencode($buff, 9);
        file_put_contents($this->outFile.'.xml.zip', $compressed);
        /*$doc = new DOMDocument('1.0','utf-8');
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->loadXML($buff);
        $errors = libxml_get_errors();
        var_dump($errors);
        $xml_string = $doc->saveXML();
        //echo $xml_string;
        file_put_contents($this->outFile.'.xml', $xml_string);*/
        return $buff;
    }
}