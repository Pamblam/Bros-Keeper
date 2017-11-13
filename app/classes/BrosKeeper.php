<?php

class BrosKeeper {
	
	public $APP_PATH = "";
	public $SETTINGS = null;
	public $config = null;
	public $db = null;
	
	public function __construct() {
		$this->APP_PATH = realpath(dirname(dirname(__FILE__)));
		$this->config = new Settings($this->APP_PATH."/config.ini"); 
		$this->SETTINGS = $this->config->getSettings();
		$this->db = DB::getInstance(
			$this->SETTINGS['_MYSQL_HOST'],
			$this->SETTINGS['_MYSQL_DB'],
			$this->SETTINGS['_MYSQL_USER'],
			$this->SETTINGS['_MYSQL_PASS']
		)->db;
	}
	
	
	
}
