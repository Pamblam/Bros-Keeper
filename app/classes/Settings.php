<?php

class Settings {
	private $path;
	private $_SETTINGS;
	
	public function __construct($path){
		$this->path = $path;
		$this->_SETTINGS = parse_ini_file($path);
	}
	
	public function getSettings(){
		return $this->_SETTINGS;
	}
	
	public function setSetting($key,$val){
		// Settings that start with and undersore 
		// can not be overwritten be overwritten publicly.
		
		if( // If the string does not start with an underscore,
			substr($key,0,2) != "_"
			// or if the key starts with an undersore, 
			|| (substr($key,0,2) == "_"
			// but that key does not exist yet,
			&& !isset($this->_SETTINGS[$key]))
		){  // then we go ahead and write it.
			$this->_SETTINGS[$key] = $val;
			self::writeINIFile($this->_SETTINGS, $this->path);
			// Otherwise, return false.
		} else return false;
		
		return true;
	}
	
	public static function writeINIFile($assoc_arr, $path) { 
		$content = "";
		foreach ($assoc_arr as $key => $elem)
			$content .=  $key ." = \"$elem\"\n";
		$handle = fopen($path, 'w+');
		if(!$handle) return false;
		$success = fwrite($handle, $content);
		fclose($handle);
		return $success;
	}
}
