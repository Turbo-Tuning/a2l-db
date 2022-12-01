<?php
/**
 * This file is part of TurboTuner/a2l-db package.
 *
 * Licensed under the GPL3 License
 * (c) TurboTuner
 * 
 * Parts borrowed from http://www.sitepoint.com/collection-classes-in-php/
 */

class KeyHasUseException extends Exception{

}

interface CollectionItemInterface{
    public function toJson($inner = false);
}

class KeyInvalidException extends Exception{

}

class ReachedMaxSizeException extends Exception{

}

class Collection implements ArrayAccess, IteratorAggregate, Countable {
    private $items = array();
    private $Type;
    private $objName;
    
    var $SortColumn;
    var $SortOrder;

    public function __construct(mixed $objType = null){
        if ($objType != null){
            if(is_class($objType)){
                $this->Type = gettype($objType);
                $obj = new $objType;
                $this->objName = get_class($obj);
            }
        }
    }

    public function __get($n) { 
        return $this[$n]; 
    }


    public function Sort(){
    }

    public function getIterator(){
        yield from $this->items;
    }
 
    public function addItem($obj, $key = null){
        return $this->offsetSet($key, $obj);
    }

    public function offsetSet($key = null, $obj) 
    {
        if ($key == null) 
        {
            $this->items[] = $obj;
        }
        else 
        {
            if (isset($this->items[$key])) 
            {
                throw new KeyHasUseException("Key $key already in use.");
            }
            else 
            {
                $this->items[$key] = $obj;
            }
        }
        return $this;
    }

    public function offsetUnset($key) 
    {
        if (isset($this->items[$key])) 
        {
            unset($this->items[$key]);
        }
        else 
        {
            throw new KeyInvalidException("Invalid key $key.");
        }
    }

    public function offsetGet($key) 
    {
        if (isset($this->items[$key])) 
        {
            return $this->items[$key];
        }
        else 
        {
            throw new KeyInvalidException("Invalid key $key.");
        }
    }

    public function keys() 
    {
        return array_keys($this->items);
    }

    public function count() 
    {
        return count($this->items);
    }

    public function offsetExists($key){
        return isset($this->items[$key]);
    }

    public function toArray(){
        return $this->items;
    }

}

/* @usage 
 

class Salut
{
    private $name;
    private $number;

    public function __construct($name, $number) 
    {
        $this->name = $name;
        $this->number = $number;
    }

    public function __toString() 
    {
        return $this->name . " is number " . $this->number;
    }
}


$c = new Collection();
$c->addItem(new Salut("Steve", 14), "steve");
$c->addItem(new Salut("Ed", 37), "ed");
$c->addItem(new Salut("Bob", 49), "bob");

$c->deleteItem("steve");

try 
{
    $c->getItem("steve");
}
catch (KeyInvalidException $e)
{
    print "The collection doesn't contain Steve.";
}

*/
?>