<?php
/*===== Kurmix - PHP =====                           _  __   www.kurmix.com   _      
* @author    Andree Ochoa <andlody@hotmail.com>     | |/ /   _ _ __ _ __ ___ (_)_  __
* @copyright 2017-2022 Andree Ochoa                 | ' / | | | '__| '_ ` _ \| \ \/ /
* @license   The MIT license                        | . \ |_| | |  | | | | | | |>  < 
* @version   1.0.2                                  |_|\_\__,_|_|  |_| |_| |_|_/_/\_\       */

require_once '_libs/kurmix/Connection.php';

abstract class Model
{
    function __construct(){}

    public function query($query,$array=null){
        return Connection::execute($query,$array);
    }

    public function save(){
        $table = $this->getNameTable();

        $columns = '';
        $simbol = '';
        $sets = '';
        $values = [];

        foreach($this->getColumns() as $v){
            $attr = $v['attr'];
            $column = $v['column'];

            $values[] = $this->$attr;
            $columns .= '`'.$column."`,";
            $simbol .= "?,";
            $sets .= '`'.$column."`=?,";
        }

        $columns = substr($columns, 0, -1);
        $simbol = substr($simbol, 0, -1);
        $sets = substr($sets, 0, -1);
        
        $nameId = $this->getNameId();
        $id = $nameId['attr'];

        if($this->$id==0 || $this->$id==null)
            $this->$id = self::query("INSERT INTO `$table` ($columns) VALUES ($simbol)",$values);
        else {
            $values[] = $this->$id;
            $identifier = $nameId['column'];
            self::query( "UPDATE `$table` SET $sets WHERE `$identifier` = ?", $values);	
        }
        return $this;
    }

    public function delete(){
        $table = $this->getNameTable();
        $nameId = $this->getNameId();

        $id = $nameId['attr'];
        $identifier = $nameId['column'];

        self::query( "DELETE FROM `$table` WHERE `$identifier` = ?", [$this->$id]);	
        return null;
    }

    public function findById($id){
        $table = $this->getNameTable();
        $identifier = $this->getNameId()['column'];

        $a = self::query("SELECT * FROM `$table` WHERE `$identifier` = ? LIMIT 1",[$id]);
        if(sizeof($a)>0) return $this->arrayToObject($a[0]);
        return null;  
    }

    public function find($key,$value){
        $table = $this->getNameTable();

        foreach($this->getColumns() as $v){
            if($attr == $v['attr'])
                $column = $v['column'];
        }

        $a = self::query("SELECT * FROM `$table` WHERE `$column` = ?",array($value));
        $x = [];
        foreach($a as $v)
           $x[] = $this->arrayToObject($v);
        
        return $x;
    }

    public function where($where,$values){
        /*preg_match_all("/@(.*?)=/s", $str, $matches);
        echo json_encode( $matches );

        preg_match_all("/@(.*?)LIKE/s", $str, $matches);
        echo json_encode( $matches );*/

        $table = $this->getNameTable();

        $a = self::query("SELECT * FROM `$table` WHERE $where",$values);
        $x = [];
        foreach($a as $v)
           $x[] = $this->arrayToObject($v);
        
        return $x;
    }

    public function toArray(){
        return json_decode(json_encode($this), true);
    }

    public function objectOf($array){
        $class = get_class($this);
        $obj = new $class;
        foreach($array as $k => $v){
            if(property_exists($class,$k))
                $obj->$k = $v;
        }
        return $obj;
    }

    protected function arrayToObject($array){
        $class = get_class($this);
        $obj = new $class;
        foreach($this->getColumns() as $v){
            $attr = $v['attr'];
            $column = $v['column'];

            $obj->$attr = $array[$column];
        }
        return $obj;
    }
  
    public function createTable(){ 
        $reflector = new ReflectionClass(get_class($this));
        $class = $reflector->getDocComment();
        $matches = [];
        if(strpos($class, "@Entity")===false) return;

        $table   = $this->getNameTable();
        $columns = $this->getColumnsCreate();

        self::query("CREATE TABLE IF NOT EXISTS `$table` ( $columns ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
    }

    protected function getColumnsCreate(){
        return $this->getColumnsGeneric(false,false);
    }

    protected function getColumns(){
        return $this->getColumnsGeneric(true,false);
    }

    protected function getNameId(){
        return $this->getColumnsGeneric(false,true);
    }

    protected function getNameTable(){
        $reflector = new ReflectionClass(get_class($this));
        $class = $reflector->getDocComment();
        $matches = [];
        preg_match("/@Table\('(.*?)'\)/s", $class, $matches);
        return (sizeof($matches)>1)? $matches[1] : get_class($this);
    }

    protected function getColumnsGeneric($isOnlyColumns,$isNameId){
        $reflector = new ReflectionClass(get_class($this));
        $props =  get_class_vars(get_class($this));
        
        $columns = '';
        $columnsNames = [];
        foreach($props as $k => $v){
            $matches = [];
            $attr = $reflector->getProperty($k)->getDocComment();
            preg_match("/@Column\('(.*?)'\)/s", $attr, $matches);
            $column = (sizeof($matches)>1)? $matches[1] : $k;
            if($isOnlyColumns) {$columnsNames[]=array('column'=>$column,'attr'=>$k); continue;}

            preg_match("/@Type\('(.*?)'\)/s", $attr, $matches);
            $type = (sizeof($matches)>1)? $matches[1] : $k;

            $id = (strpos($attr, "@Id")===false)? '' : "AUTO_INCREMENT PRIMARY KEY";
            if($id!='' && $isNameId) return array('column'=>$column,'attr'=>$k);

            $null = (strpos($attr, "@Null")===false)? "NOT NULL" : "NULL";

            preg_match("/@Default\((.*?)\)/s", $attr, $matches);
            $default = (sizeof($matches)>1)? "default ".$matches[1] : '';

            $columns .= "`$column` $type $id $null $default,";
        }

        if($isOnlyColumns) return $columnsNames;

        return substr($columns, 0, -1);
    }
}