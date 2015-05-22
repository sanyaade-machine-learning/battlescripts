<?php 
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1){
		header( "Location: /index.php?error=Your are not connected or your session has expired" );
		exit();
}
?>
Hello, Welcome to Battle Scripts!