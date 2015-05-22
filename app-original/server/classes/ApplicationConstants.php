<?php
class ApplicationConstants {
	/* Database Connection Details */
	const DB_USER = "battle_bs";
	const DB_PASSWORD = 'bs122013';
	const DB_NAME = "battle_battlescripts";
	const IP = "www.battlescripts.net";
	
	const TYPE_GAME="G";
	const TYPE_VERSION="V";
	
	
	public static function DSN() {
	 return "mysql:host=".ApplicationConstants::IP.";dbname=".ApplicationConstants::DB_NAME;   
	}

	
	const LOG_FILE="logs/bs.log";
	
	/*Auth Constants (Kalyan)*/
	
	/* DB Constants
	const DB_URL = "localhost";
	const DB_USERNAME = "root";
	const DB_PASSWORD = "root";
	const DB_NAME = "battle_battlescripts"; */
	
	/*Auth constants*/
	const BASE_URL = "http://localhost/hybridauth/";
	const GOOGLE_ID = "260267696701.apps.googleusercontent.com";
	const GOOGLE_SECRET = "fV8-Arv9QBVj2OykoQam50nG";
	const FACEBOOK_ID = "724302940915345";
	const FACEBOOK_SECRET = "0ebc55356af0a9be2b62e1fcfa582929";
	const GITHUB_ID = "5cd2a83c2055dd59a636";
	const GITHUB_SECRET = "5f832965409afab9e5820a82a141c3725e1452a6";
	
	
	/*Auth constants Prod
	const BASE_URL = "http://battlescripts.net/hybridauth/";
	const GOOGLE_ID = "183201897579-aasuedofjtnem8aktuq528lv271u3gtq.apps.googleusercontent.com";
	const GOOGLE_SECRET = "jGc8SRxpf2z0KY1_iEV9HA_l";
	const FACEBOOK_ID = "222928341227986";
	const FACEBOOK_SECRET = "daacc4e6452b4dcc2822e79dc9c96412";
	const GITHUB_ID = "9b06740c143c32dbf9a6";
	const GITHUB_SECRET = "a2f78303701720dcfc5c3211ebc146a1e7c189ec";
	*/
	
	/*
	 * Validate Access Token
	 */
	const GOOGLE_VALIDATE_TOKEN_URL = "https://www.googleapis.com/oauth2/v1/tokeninfo?";
	const FACEBOOK_VALIDATE_TOKEN_URL = "https://graph.facebook.com/me?";
	const GITHUB_VALIDATE_TOKEN_URL = "https://api.github.com/user?";
	const GITHUB_VALIDATE_URL = "https://api.github.com/";
	
	/* Email Constants */
	const EMAIL_HOST = "ssl://soc.socialfixer.com";
	const EMAIL_PORT = 465;
	const SMTP_USERNAME = "admin@battlescripts.net";
	const SMTP_PASSWORD = "bs122013!";
	const WEBMASTER_EMAIL = "admin@socialfaker.com";
	const WEBMASTER_NAME = "Battle Scripts";

}
?>