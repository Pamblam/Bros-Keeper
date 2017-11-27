<?php

session_start();
require "classes/Settings.php";
require "classes/DB.php";
require "classes/Crypto.php";
require "classes/BrosKeeper.php";
require "classes/User.php";

$BK = new BrosKeeper();
$SETTINGS = $BK->SETTINGS;

// Create the return array
$return = array(
	"response" => "Success",
	"success" => true,
	"data" => array()
);

// Make sure an action parameter has been sent to this endpoint
checkParams(array("action"));

switch($_REQUEST['action']){
		
	case "check_session":
		$return['data'] = getCurrentUser();
		output();
		break;
	
	case "check_login":
		checkParams(array("email", "pass"));
		$pass = Crypto::encrypt($_REQUEST['pass']);
		$user = User::validateLogin($BK->db, $_REQUEST['email'], $pass);
		if(false === $user) error("Invalid email or password.");
		$_SESSION['user_id'] = $user->id;
		$return['data'] = $user;
		output();
		break;
		
	case "logout":
		$_SESSION = array();
		session_destroy();
		output();
		break;
	
	case "upload_img":
		require "functions/imgur_anon_upload.php";
		$client_id = $SETTINGS['_IMGUR_CLIENT_ID'];
		foreach($_FILES['fileUploadFiles']['tmp_name'] as $img_path){
			$url = imgur_anon_upload($client_id, $img_path);
			if($url !== false) $return['data'][] = $url;
		}
		output();
		break;
	
	case "add_todo":
		checkParams(array('parent', 'title', 'desc', 'due', 'completed', 'tags'));
		$user = getCurrentUser();
		$q = $BK->add_todo($user, $_REQUEST['parent'], $_REQUEST['title'], $_REQUEST['desc'], $_REQUEST['due'], $_REQUEST['completed'], $_REQUEST['tags']);
		$msg = count($BK->errors) > 0 ? $BK->errors[0] : "Could not add to-do item.";
		if(!$q) error($msg);
		output();
		break;
		
	case "get_todos":
		$user = getCurrentUser();
		$q = $BK->get_todos($user);
		$msg = count($BK->errors) > 0 ? $BK->errors[0] : "Could not gather to-do items.";
		if(!$q) error($msg);
		$return['data'] = $q;
		output();
		break;
		
	default: error("Error: invalid action parameter");
}

function checkParams($reqd){
	foreach($reqd as $param)
		if(!isset($_REQUEST[$param])) 
			error("Error: Missing $param parameter.");
}

function error($oopsie){
	$GLOBALS['return']['response'] = $oopsie;
	$GLOBALS['return']['success'] = false;
	$GLOBALS['return']['data'] = array();
	output();
}

function output(){
	header("Content-Type: application/json");
	echo json_encode($GLOBALS['return']);
	exit;
}

function getCurrentUser(){
	global $BK;
	if(!isset($_SESSION['user_id'])) error("Please log in.");
	return new User($BK->db, $_SESSION['user_id']);
}