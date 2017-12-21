<?php

class BrosKeeper {
	
	public $APP_PATH = "";
	public $SETTINGS = null;
	public $config = null;
	public $db = null;
	public $errors = array();
	
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
		if(empty($user) || empty($user->id)) return false;
		$user_id = $user->id;
		$parent = is_numeric($parent) ? $parent : null;
		if(empty($title)) $title = "Undefined";
		if(empty($desc)) $desc = "";
		$due = self::getInsertableDate($due);
		$completed = "false" !== $completed && !empty($completed) ? $completed = date("Y-m-d") : null;
		$created = date("Y-m-d");
		$params = array($user_id, $parent, $title, $desc, $created, $due, $completed, $tags);
		$q = $this->db->prepare("INSERT INTO `todo` (`user_id`, `parent`, `name`, `desc`, `entered_at`, `due_date`, `completed_at`, `tags`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
		return !!$q->execute($params);
	}
	
	public function edit_todo($user, $id, $title, $desc, $due, $completed, $tags){
		if(empty($user) || empty($user->id)) return false;
		$user_id = $user->id;
		if(empty($title)) $title = "Undefined";
		if(empty($desc)) $desc = "";
		$due = self::getInsertableDate($due);
		$completed = "false" !== $completed && !empty($completed) ? $completed = date("Y-m-d") : null;
		$created = date("Y-m-d");
		$params = array($title, $desc, $created, $due, $completed, $tags, $user_id, $id);
		$q = $this->db->prepare("UPDATE `todo` SET `name` = ?, `desc` = ?, `entered_at` = ?, `due_date` = ?, `completed_at` = ?, `tags` = ? WHERE `user_id` = ? AND `id` = ?");
		return !!$q->execute($params);
	}
	
	public function delete_todo($user, $id){
		if(empty($user) || empty($user->id)) return false;
		$user_id = $user->id;
		$q = $this->db->prepare("DELETE FROM `todo` WHERE `user_id` = ? AND (`id` = ? OR `parent` = ?)");
		$params = array($user->id, $id, $id);
		return !!$q->execute($params);
	}
	
	public function get_todos($user, $parent=null){
		if(empty($user) || empty($user->id)) return false;
		$parent = is_numeric($parent) ? $parent : null;
		$user_id = $user->id;
		$q = $this->db->prepare("SELECT * FROM `todo` WHERE `user_id` = ? and `parent` = ? OR (`parent` IS NULL AND ? IS NULL)");
		$q->execute(array($user_id, $parent, $parent));
		$todos = array();
		while($td = $q->fetch(PDO::FETCH_ASSOC)){
			$td['children'] = $this->get_todos($user, $td['id']);
			$td['tags'] = empty($td['tags']) ? array() : explode(",", $td['tags']);
			$td['tags'] = array_map('trim', $td['tags']);
			unset($td['parent']);
			$todos[] = $td;
		}
		return $todos;
	}
	
	public static function getInsertableDate($date){
		if(empty($date)) return null;
		$chunks = explode("/", $date);
		if(count($chunks) !== 3) return null;
		if(strlen($chunks[0]) !== 2) return null;
		if(strlen($chunks[1]) !== 2) return null;
		if(strlen($chunks[2]) !== 4) return null;
		return "{$chunks[2]}-{$chunks[0]}-{$chunks[1]}";
	}
	
}