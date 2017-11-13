<?php

if (php_sapi_name() != "cli") die("Please run this script from command line.");

echo <<<ooo

-------------------------------------
| Welcome to Bro's Keeper setup <3 |
-------------------------------------

 Step 1. Set up MySQL Connection...

ooo;

$mysql = getValidatedPDOFromUser();
$pdo = $mysql['pdo'];

$db = getValidatedDBFromUser($pdo);

echo <<<ooo

 Step 2. Security

ooo;

$key = getInput("\n - Enter an encryption key (you don't need to remember this, but it will be in your config file): ");

echo <<<ooo

 Step 3. Creating the tables

ooo;

initDatabase($pdo);

echo <<<ooo


 Step 4. Writing the ini file

ooo;

initConfig($mysql['input'], $db, $key);
$SETTINGS = getSettings();

echo <<<ooo


 Step 5. Create a user account

ooo;

createNewUser($pdo, $key);

echo <<<ooo

Successfully installed Bro's Keeper.

ooo;

exit;

////////////////////////////////////////////////////////////////////////////////

function createNewUser(&$pdo, $key){
	$u = getUserAccountInput();
	$password = encrypt($u['password']);
	try{
		$pdo->exec("INSERT INTO `users` (`name`, `email`, `password`) 
			VALUES ('{$u['name']}', '{$u['email']}', '$password');");
	}catch(PDOException $e){
		echo "\n\n! Error: Could not insert user !";
		exit;
	}
}

function getSettings(){
	$app = realpath(dirname(dirname(__FILE__)));
	require_once($app."/classes/Settings.php");
	$s = new Settings("$app/config.ini");
	return $s->getSettings();
}

function encrypt($string){
	global $SETTINGS;
	$app = realpath(dirname(dirname(__FILE__)));
	require_once($app."/classes/Crypto.php");
	return Crypto::encrypt($string);
}

function getUserAccountInput(){
	$name = getInput("\n - Enter your NAME: ");
	$email = getInput("\n - Enter your EMAIL: ");
	$password = getInput("\n - Enter a PASSWORD: ");
	return array(
		"name" => $name,
		"email" => $email,
		"password" => $password
	);
}

function initConfig($mysql, $db, $key){
	$path = realpath(dirname(dirname(__FILE__)))."/config.ini";
	$s = writeINIFile(array(
		"_MYSQL_HOST" => $mysql['host'],
		"_MYSQL_USER" => $mysql['user'],
		"_MYSQL_PASS" => $mysql['password'],
		"_MYSQL_DB" => $db,
		"_CRYPTOKEY" => trim($key)
	), $path);
	if(!$s){
		echo "\n\n!! Could not write to config file at $path - ensure appropriate permissions. !!\n";
		exit;
	}else{
		echo "\n - Config successfully set up at $path";
	}
}

function getValidatedPDOFromUser(){
	$m = gatherMySQLInput();
	while(($pdo = getPDO($m['host'], $m['user'], $m['password'])) === false){
		echo "\n\n!! Could not connect to MySQL using that username and password !!\n";
		$m = gatherMySQLInput();
	}
	return array(
		"input" => $m,
		"pdo" => $pdo
	);
}

function gatherMySQLInput(){
	$mysql_host = getInput("\n - Enter MySQL HOST: ");
	$mysql_user = getInput("\n - Enter MySQL USER: ");
	$mysql_pass = getInput("\n - Enter MySQL PASSWORD: ");
	return array(
		"host" => $mysql_host,
		"user" => $mysql_user,
		"password" => $mysql_pass
	);
}

function getValidatedDBFromUser(&$pdo){
	$in = gatherDBInput();
	if(!$in['exists'] && !createDB($pdo, $in['name'])){
		echo "\n\n! Error: Could not create the database with the given user. Please create the database manually with the root user and try again or try again using an existing database that this user has permission to access !";
		exit;
	}
	//if($in['exists'] && confirmPurgeDB($pdo, $in['name'])) $in['exists'] = false;
	if(!useDB($pdo, $in['name'])){
		echo "\n\n! Error: Could not validate the database with the given user. Please create the database with the root user and try again or try again using an existing database that this user has permission to access !";
		exit;
	}
	return $in['name'];
}

function confirmPurgeDB(&$pdo, $name){
	$purge_input = getInput("\n - Do you want to purge the existing database and install fresh? Type [Y|y] to purge, type anything else to keep existing data. An affirmative input will result in ALL TALBES being deleted from this database: ");
	$purge = strtoupper(trim($purge_input)) !== "Y";
	if(!$purge) return false;
	try{
		$pdo->exec("DROP DATABASE $name;");
	}catch(PDOException $e){
		echo "\n\n! Error: Could not drop database !";
		exit;
	}
	if(!createDB($pdo, $name)){
		echo "\n\n! Error: Could not re-create the database !";
		exit;
	}
	return true;
}

function initDatabase(&$pdo){
	$sql = file_get_contents(realpath(dirname(__FILE__))."/setup.sql");
	if(empty($sql)){
		echo "\n\n! Error: setup.sql missing or empty !";
		exit;
	}
	try{
		$pdo->exec($sql);
	}catch(PDOException $e){
		echo "\n\n! Error: Could not create tables !";
		exit;
	}
	echo "\n - Database initialized :)\n - Tables created :P";
}

function createDB(&$pdo, $name){
	try{
		$pdo->exec("CREATE DATABASE $name;");
	}catch(PDOException $e){
		return false;
	}
	return true;
}

function useDB(&$pdo, $name){
	try{
		$pdo->exec("USE $name;");
	}catch(PDOException $e){
		return false;
	}
	return true;
}

function gatherDBInput(){
	$db_exists_input = getInput("\n - Do you want to create a new database for this instance? Type [Y|y] to create new database, type anything else to use an existing databse: ");
	$db_exists = strtoupper(trim($db_exists_input)) !== "Y";

	$db_msg = $db_exists ? 
		"\n - Enter the name of the existing database to use for this app: " :
		"\n - Enter the name of the database to be created for this app: " ;
	$mysql_db = getInput($db_msg);
	return array(
		"name" => $mysql_db,
		"exists" => $db_exists
	);
}

function getInput($text){
	if (PHP_OS == 'WINNT') {
		echo $text;
		$line = stream_get_line(STDIN, 1024, PHP_EOL);
	} else {
		$line = readline($text);
	}
	return $line;
}

function getPDO($h, $u, $p){
	try {
        $dbh = new PDO("mysql:host=$h", $u, $p);
		$dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
    } catch (PDOException $e) {
        $dbh = false;
    }
	return $dbh;
}

function writeINIFile($assoc_arr, $path) { 
	$app = realpath(dirname(dirname(__FILE__)));
	require_once($app."/classes/Settings.php");
	return Settings::writeINIFile($assoc_arr, $path);
}