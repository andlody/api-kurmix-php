<?php
/*===== Kurmix - PHP =====                           _  __   www.kurmix.com   _      
* @author    Andree Ochoa <andlody@hotmail.com>     | |/ /   _ _ __ _ __ ___ (_)_  __
* @copyright 2017-2022 Andree Ochoa                 | ' / | | | '__| '_ ` _ \| \ \/ /
* @license   The MIT license                        | . \ |_| | |  | | | | | | |>  < 
* @version   1.0.2                                  |_|\_\__,_|_|  |_| |_| |_|_/_/\_\       */

class Connection
{	
	protected static function start(){
		
        $aux = 'mysql'.':host='.Config::HOST.';port='.Config::PORT.';dbname='.Config::DATABASE.';charset=utf8';
		
		try {
            $con = new PDO($aux, Config::USER,Config::PASS);
            return $con;
        }catch(PDOException $e){
        	die($e->getMessage());
        }
	}

    public static function execute($sql,$params=null){
        $con = self::start();
        
        $stmt = $con->prepare($sql);
        if($params==null)
            $stmt->execute();
        else              
            $stmt->execute($params);

        $error = $stmt->errorInfo();
        if($error[0] != 0){
            die($error[2]);
        }

        $lastId = $con->lastInsertId();
        if($lastId!="0") return $lastId;

        $list = array();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        for($i=0;$i<sizeof($row);$i++){
            foreach ($row[$i] as $key => $v){
                $list[$i][$key]=$v;
            }
        }
        $con=null;
        return $list;     
    }
}
