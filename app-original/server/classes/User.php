<?php
class User {
	public $id;
	public $firstName;
	public $lastName;
	public $authProvider;
	public $authId;
	public $email;
	public $superUser=false;
	public $games=array();
	public $players=array();
}
?>