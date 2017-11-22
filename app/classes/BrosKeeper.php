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
	
	public function add_todo($user, $parent, $title, $desc, $due, $completed, $tags){
		$this->db->prepare("INSERT INTO `todo` (`user_id`, `parent`, `name`, `desc`, `entered_at`, `due_date`, `completed_at`, `tags`) VALUES (?, ?, ?, ?, ?, ?, ?)");
	}
	
}