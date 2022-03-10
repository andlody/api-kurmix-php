<?php
/*===== Kurmix - PHP =====                           _  __   www.kurmix.com   _      
* @author    Andree Ochoa <andlody@hotmail.com>     | |/ /   _ _ __ _ __ ___ (_)_  __
* @copyright 2017-2022 Andree Ochoa                 | ' / | | | '__| '_ ` _ \| \ \/ /
* @license   The MIT license                        | . \ |_| | |  | | | | | | |>  < 
* @version   1.0.2                                  |_|\_\__,_|_|  |_| |_| |_|_/_/\_\       */

abstract class Controller {

    function write($array,$status=200,$headers=null){
        Router::print($array,$status,$headers);
    }

    function getMethod(){
        return $_SERVER['REQUEST_METHOD'];
    }

    function setMethod($method){
        if(strtoupper($method)=='ANY')
            throw new Exception('No existe la accion con metodo: ['.$_SERVER['REQUEST_METHOD'].']');

        if($_SERVER['REQUEST_METHOD']!=strtoupper($method))
            throw new Exception('No existe la accion con metodo: ['.$_SERVER['REQUEST_METHOD'].']');
    }

    function getBody(){        
        return json_decode(file_get_contents('php://input'), true);
    }

    function model($model){
		if (!file_exists ('app/model/'.$model.'.php')){ 
			$this->error();
		}
		require_once('app/model/'.$model.'.php');
		return new $model();
    }

    function getHeader($name){
        foreach (getallheaders() as $k => $v) {
            if( strtoupper($name) == strtoupper($k) )
                return $v;
        }
    }
}