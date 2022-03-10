<?php
/*===== Kurmix - PHP =====                           _  __   www.kurmix.com   _      
* @author    Andree Ochoa <andlody@hotmail.com>     | |/ /   _ _ __ _ __ ___ (_)_  __
* @copyright 2017-2022 Andree Ochoa                 | ' / | | | '__| '_ ` _ \| \ \/ /
* @license   The MIT license                        | . \ |_| | |  | | | | | | |>  < 
* @version   1.0.2                                  |_|\_\__,_|_|  |_| |_| |_|_/_/\_\       */

class Router 
{
	static function main()
	{ 
		require 'app/_config/Config.php';

		try {
			self::route();
		}catch (Throwable $t){
			$error['message'] = $t->getMessage();
			$error['detail']['file'] = $t->getFile().': '.$t->getLine();
			$error['detail']['trace'] = $t->getTrace();
		}catch (Exception $e) {
			$error['message'] = $e->getMessage();
			$error['detail']['file'] = $e->getFile().': '.$e->getLine();
			$error['detail']['trace'] = $e->getTrace();
		}finally{
			if(!isset($error)) return; 			
			if(!Config::DEV) unset($error['detail']);
			self::print($error,500);
		}
	}

	static function route()
	{ 
		require '_libs/kurmix/Model.php';
		if($_GET['k']=='run-orm') return self::runORM();

		$action_name = 'index';
		$controller_name = 'index';
		if(!empty($_GET['k'])){
			$a = explode('/',$_GET['k']);
			if(sizeof($a)>0) $controller_name = $a[0];
			if(sizeof($a)>1) $action_name = $a[1];
		}

		if (!file_exists ('app/controller/'.$controller_name.'_controller.php')) {
			throw new Exception('No existe el controlador: ['.$controller_name.']');
		}
		
		require '_libs/kurmix/Controller.php';
		require 'app/controller/'.$controller_name.'_controller.php';
		$controlador_class = $controller_name.'_controller';
		$controller = new $controlador_class();
		
		if (!method_exists($controller,$action_name)){
			throw new Exception('No existe la acción ['.$action_name.'] en el controlador ['.$controller_name.']');
		}
        
		$rf = new ReflectionMethod($controller_name.'_controller', $action_name);
		$n = $rf->getNumberOfParameters();        

		$params = array();
		$j=2;
        for ($i = 0; $i < $n; $i++) {   
            $params[$i] = ($j<sizeof($a))?$a[$j]:"";  
            $j++;
        }

		call_user_func_array(array($controller, $action_name), $params);
	}

	static function print($array,$status,$headers){
        header('Content-Type: application/json; charset=utf-8');
		foreach($headers as $v)
			header($v);

        http_response_code($status);
        echo json_encode($array);
	}

	static function runORM(){ 
		if(!Config::DDL) return self::print(['message'=>'No procesado'],401);

		$list = scandir( 'app/model' ); 
		foreach($list as $v){
			if($v=='.' || $v=='..') continue;
			require 'app/model/'.$v;
			$class = str_replace('.php','',$v);
			$obj = new $class();
			if(method_exists($obj,'createTable'))
				$obj->createTable();
		}
		self::print(['message'=>'Procesado'],200);
	}
}