<?php

class User {
	
	public $id = 0;
	public $name = "";
	public $email = "";
	private $db = null;
	
	public function construct($db, $id){
		$this->db = $db;
		$q = $this->db->prepare("SELECT * FROM `users` WHERE `id` = ?");
		$q->execute([$id]);
		$res = $q->fetch(PDO::FETCH_ASSOC);
		if(!$res) error("No user found with ID $id.");
		$this->id = $res['id'];
		$this->name = $res['name'];
		$this->email = $res['email'];
	}
	
	public static function validateLogin($db, $email, $password){
		$q = $db->prepare("SELECT `id` from `users` WHERE `email` = ? and `password` = ?");
		$q->execute([$email, $password]);
		$res = $q->fetch(PDO::FETCH_ASSOC);
		if(!$res) return false;
		return new User($db, $res['id']);
	}
}
