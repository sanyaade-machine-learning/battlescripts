<?php
include_once 'ApplicationConstants.php';
class DBConnect{
	private $con;
	function __construct(){
	}
	
	function executeQuery($query){ 
		$mysqli = new mysqli(ApplicationConstants::DB_URL,ApplicationConstants::DB_USERNAME,ApplicationConstants::DB_PASSWORD,ApplicationConstants::DB_NAME);
		// Check connection
		if (mysqli_connect_errno($this->con))
		{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
			exit;
		}
		$result = $mysqli->query($query);
		if (!$result) {
	    	die('Invalid query: ' . mysql_error());
		}
		return $result;
	}
	function executeUpdate($query){
		$mysqli = new mysqli(ApplicationConstants::DB_URL,ApplicationConstants::DB_USERNAME,ApplicationConstants::DB_PASSWORD,ApplicationConstants::DB_NAME);
		// Check connection
		if (mysqli_connect_errno($this->con))
		{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
			exit;
		}
		$result = $mysqli->query($query);
		$id= $mysqli->insert_id;
		return $id;
	}
	
}
?>