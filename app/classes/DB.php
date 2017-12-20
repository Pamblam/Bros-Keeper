<?php

class DB {
    public $db;
    private static $instances = [];
 
    private function __construct($host, $db, $user, $pass){
        $this->db = new PDO(
				"mysql:host=$host;".
				"dbname=$db;".
				'charset=utf8', 
				$user, $pass);
    }
	
    public static function getInstance($host, $db, $user, $pass){
		$key = md5(json_encode(array($host, $db, $user, $pass)));
        if (!isset(self::$instances[$key])){
            $object = __CLASS__;
            self::$instances[$key] = new $object($host, $db, $user, $pass);
        }
        return self::$instances[$key];
    }
}