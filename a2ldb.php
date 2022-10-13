<?php

class a2ldb{

    var $db;
    var $dbfile;

    public function __construct($database_name, $force=true){
        
        $outfile = $database_name;
        $this->dbfile = $outfile;
		if ((file_exists($outfile)) and ($force == true)){
			unlink($outfile);
		}

        if (!file_exists($outfile)){
            $this->db = new SQLite3DB($outfile);
        
            $sql = file_get_contents('a2ltables.sql');
            $sqls = explode(";", $sql);
            
            foreach($sqls as $sql){
                if ($sql != "")  $x = $this->db->query($sql);
            }
		
        } else {
            $this->db = new SQLite3DB($outfile);
        }

        $this->db->query("BEGIN");
    }

    public function __destruct()
    {
        $this->db->query("COMMIT");
    }

    public function Insert_CompuMethod($v){
        return $this->Insert('compu_methods', $v);
    }

    public function Insert_Characteristic($v){
        return $this->Insert('characteristics', $v);
    }

    public function Insert($tablename, $a){
        $nope = array('|||');
        if (!$this->Found($tablename, $a)){
            $fields = $this->getFields($a);
            $vals = $this->getValues($a);
            $sql = 'INSERT INTO '.$tablename.' ('.$fields.') VALUES ('.$vals.')';
            foreach($nope as $kkk => $vvv){
                if(substr_count($sql, $vvv) > 0){
                    echo('Error parsing A2l '.$sql);
                    echo('DB: '.$this->dbfile);
                    die('Miserable death');
                }
            }
            
            return $this->exec($sql);
        }
    }

    private function Found($tablename, $a){
        if ((strcasecmp($tablename, 'def_characteristics') != 0) and (strcasecmp($tablename, 'ref_characteristics') != 0)){
            $sql = 'SELECT * FROM '.$tablename.' WHERE name="'.trim($a['name'], '"').'"';
            try{
                $r = $this->db->get_rows($sql);
                if(count($r) > 0){
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e){
                return false;
            }
        } else {
            return true;
        }
    }

    private function exec($sql){
        try{
            $this->db->query($sql);
            return $this->db->insert_id();
        } catch (Exception $e){
            return false;
        }
        

    }

    private function getFields($v){
        $f = '';
        $c = count($v);
        $k = array_keys($v);
        for($n=0; $n<$c-1; $n++){
            $f .= $k[$n].', ';
        }

        //add last field
        $f .= $k[$c-1];
        return $f;
    }

    private function getValues($v){
        $f = '';
        $c = count($v);
        $r = array_values($v);
        for($n=0; $n<$c-1; $n++){
            $f .= '"'.htmlspecialchars(trim($r[$n],'"')).'", ';
        }
        //add last value
        $f .= '"'.htmlspecialchars(trim($r[$n],'"')).'"';
        return $f;
    }
}

?>