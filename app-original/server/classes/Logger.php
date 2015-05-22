<?php
//require_once $_SERVER["DOCUMENT_ROOT"].'/server/includes/UtilityFunctions.php';
class Logger {
	/**
	 * 
	 * log a message to the log file.  Format:
	 * 
	 * YYYY-mm-dd HH:MM:SS	message
	 * @param String $message
	 * @return void
	 */
	public static function log($message) {
		try {
			$handle = fopen(ApplicationConstants::LOG_FILE, "a");
			fwrite($handle, date("Y-m-d H:i:s")."\t$message\n\r");
			fclose($handle);
		} catch (Exception $ex) {
			if ($handle != null) {
				fclose($handle);
			}
		}
	}
	
	public static function clickTrack($category,$itemId,$attributes) {
		$db=DataBroker::singleton();
		
		$sql  = "INSERT INTO OA_CLICK_TRACK (TRACK_CATEGORY,ITEM_ID,ATTRIBUTES) VALUES (:cat,:id,:attr)";
		
		$values=array();
		$values[":cat"]=$category;
		$values[":id"]=$itemId;
		$values[":attr"]=$attributes;
		
		$stmt = $db->prepare($sql);
		try {
			$stmt->execute($values);
		} catch (Exception $ex) {
			//Just squash it
		}
		
	}
}
?>