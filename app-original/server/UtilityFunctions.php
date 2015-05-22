<?php
session_start();
function my_autoloader($class) {
    if (!is_file($_SERVER["DOCUMENT_ROOT"]."/server/classes/".$class . '.php')) {
    	return false;
    }
	require_once $_SERVER["DOCUMENT_ROOT"]."/server/classes/".$class . '.php';
}
spl_autoload_register('my_autoloader');
?>