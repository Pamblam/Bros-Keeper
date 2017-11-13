<?php

class Crypto {
  
	private static function getKey(){
		global $SETTINGS;
		return $SETTINGS['_CRYPTOKEY'];
	}
  
    private static function getIv(){
    	$secret_iv = md5(md5(self::getKey()));
        return substr(hash('sha256', $secret_iv), 0, 16);
    }
  
    public static function encrypt($string) {
		$output = openssl_encrypt($string, "AES-256-CBC", self::getKey(), 0, self::getIv());
        return base64_encode($output);
    }
	
    public static function decrypt($string) {
        return openssl_decrypt(base64_decode($string), "AES-256-CBC", self::getKey(), 0, self::getIv());
    }
}