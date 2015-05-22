<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/server/UtilityFunctions.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/server/classes/ApplicationConstants.php';
class DataBroker 
{  
    /**
     * The singleton instance
     * 
     */
    static private $DBInstance; 
     
  	/**
  	 * Creates a PDO instance representing a connection to a database and makes the instance available as a singleton
  	 * 
  	 * @param string $dsn The full DSN, eg: mysql:host=localhost;dbname=testdb
  	 * @param string $username The user name for the DSN string. This parameter is optional for some PDO drivers.
  	 * @param string $password The password for the DSN string. This parameter is optional for some PDO drivers.
  	 * @param array $driver_options A key=>value array of driver-specific connection options
  	 * 
  	 * @return PDO
  	 */
    public function __construct() 
    {
       if(!self::$DBInstance) { 
	        try {
			   self::$DBInstance = new PDO(ApplicationConstants::DSN(), ApplicationConstants::DB_USER, ApplicationConstants::DB_PASSWORD);
			   self::$DBInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) { 
			   //Logger::log("CONNECTION ERROR: " );
			   die("Unable to connect to the database: ". $e->getMessage());
			}
    	}  	
    }
    
    public static function singleton()
    {
        if(!self::$DBInstance) { 
	        try {
			   self::$DBInstance = new PDO(ApplicationConstants::DSN(), ApplicationConstants::DB_USER, ApplicationConstants::DB_PASSWORD);
			   self::$DBInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			} catch (PDOException $e) { 
			   //Logger::log("CONNECTION ERROR: " . $e->getMessage());
			   die("Unable to connect to the database: ". $e->getMessage());
			}
    	}
      	return self::$DBInstance;   	
    }

  	/**
  	 * Initiates a transaction
  	 *
  	 * @return bool
  	 */
	public function beginTransaction() {
		return self::$DBInstance->beginTransaction();
	}
        
	/**
	 * Commits a transaction
	 *
	 * @return bool
	 */
	public function commit() {
		return self::$DBInstance->commit();
	}

	/**
	 * Fetch the SQLSTATE associated with the last operation on the database handle
	 * 
	 * @return string 
	 */
    public function errorCode() {
    	return self::$DBInstance->errorCode();
    }
    
    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * @return array
     */
    public function errorInfo() {
    	return self::$DBInstance->errorInfo();
    }
    
    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $statement
     */
    public function exec($statement) {
    	return self::$DBInstance->exec($statement);
    }
    
    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute($attribute) {
    	return self::$DBInstance->getAttribute($attribute);
    }

    /**
     * Return an array of available PDO drivers
     *
     * @return array
     */
    public function getAvailableDrivers(){
    	return Self::$DBInstance->getAvailableDrivers();
    }
    
    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param string $name Name of the sequence object from which the ID should be returned.
     * @return string
     */
	public function lastInsertId($name) {
		return self::$DBInstance->lastInsertId($name);
	}
        
   	/**
     * Prepares a statement for execution and returns a statement object 
     *
     * @param string $statement A valid SQL statement for the target database server
     * @param array $driver_options Array of one or more key=>value pairs to set attribute values for the PDOStatement obj 
returned  
     * @return PDOStatement
     */
    public function prepare ($statement, $driver_options=false) {
    	if(!$driver_options) $driver_options=array();
    	return self::$DBInstance->prepare($statement, $driver_options);
    }
    
    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $statement
     * @return PDOStatement
     */
    public function query($statement) {
    	return self::$DBInstance->query($statement);
    }
    
    /**
     * Execute query and return all rows in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchAllAssoc($statement) {
    	return self::$DBInstance->query($statement)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Execute query and return one row in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function queryFetchRowAssoc($statement) {
    	return self::$DBInstance->query($statement)->fetch(PDO::FETCH_ASSOC);    	
    }
    
    /**
     * Execute query and select one column only 
     *
     * @param string $statement
     * @return mixed
     */
    public function queryFetchColAssoc($statement) {
    	return self::$DBInstance->query($statement)->fetchColumn();    	
    }
    
    /**
     * Quotes a string for use in a query
     *
     * @param string $input
     * @param int $parameter_type
     * @return string
     */
    public function quote ($input, $parameter_type=0) {
    	return self::$DBInstance->quote($input, $parameter_type);
    }
    
    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public function rollBack() {
    	return self::$DBInstance->rollBack();
    }      
    
    /**
     * Set an attribute
     *
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($attribute, $value  ) {
    	return self::$DBInstance->setAttribute($attribute, $value);
    }
    
    public static function createInsertSQL($a)
    {
    	$fields=array();
    	$values=array();
    	foreach ($a as $field=>$val)
    	{
    		array_push($fields, $field);
    		if (is_numeric($val))
    		{
    			array_push($values, $val);
    		} else
    		{
    			 if (empty($val))
    			{
    				array_push($values, "NULL");
    			} else
    			{
    				array_push($values, "'".str_replace("'", "''", stripslashes($val))."'");
    			}
    		}
    	}
    	return "(".implode(",", $fields).") values (".implode(",", $values).")";
    }
    public static function createUpdateSQL($a)
    {
    	$updates=array();
    	foreach ($a as $field=>$val)
    	{
    		$s=$field."=";
    		if (is_numeric($val))
    		{
    			$s .= $val;
    		} else
    		{
    			if (empty($val))
    			{
    				$s .= "NULL";
    			} else
    			{
    				$s .="'".str_replace("'", "''", stripslashes($val))."'";
    			}
    		}
    		array_push($updates, $s);
    	}
    	return implode(",",$updates);
    }    
}

?>