<?php
require_once $_SERVER["DOCUMENT_ROOT"].'/server/UtilityFunctions.php';
class BattleBroker {
	protected  $db; //DataBroker

	function __construct() {
		$this->db=DataBroker::singleton();
	}

	//TODO: See if doctrine would help with this
	public function playerColumnMap() {
		return array(
		"description"=>"PLAYER_DESCRIPTION",
		"gameId"=>"GAME_ID",
		"id"=>"PLAYER_ID",
		"name"=>"PLAYER_NAME",
		"source"=>"SOURCE",
		"version"=>"PLAYER_VERSION",
		"publishedOn"=>"PUBLISHED_ON",
		"publishedSource"=>"PUBLISHED_SOURCE",
		"defaultOpponentFlag"=>"DEFAULT_OPPONENT_FLAG",
		"defaultTemplateFlag"=>"DEFAULT_TEMPLATE_FLAG",
		"testOpponentFlag"=>"TEST_OPPONENT_FLAG",
		"updatedOn"=>"UPDATED_ON"
		);
	}

	public function gameColumnMap() {
		return array(
		"id"=>"GAME_ID",
		"name"=>"NAME",
		"synopsis"=>"SYNOPSIS",
		"description"=>"DESCRIPTION",
		"private"=>"PRIVATE_FLAG",
		"minPlayers"=>"MIN_PLAYERS",
		"maxPlayers"=>"MAX_PLAYERS",
		"version"=>"VERSION",
		"difficultyRating"=>"DIFFICULTY_RATING",
		"icon"=>"ICON",
		"screenshot"=>"SCREENSHOT",
		"updatedOn"=>"UPDATED_ON",
		"source"=>"SOURCE",
		"rules"=>"RULES",
		"canvas"=>"CANVAS"
		);
	}
	//User Stuff
	/**
	 * Get a user
	 * @param int $id
	 * @return User
	 */
	public function retrieveUser($id) {
		try {
			$sql  = "SELECT * FROM USER WHERE USER_ID=:uid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":uid"=>$id));
			$row=$stmt->fetch(PDO::FETCH_ASSOC);
			$user=new User();
			$this->populateUser($user, $row,true);
			return $user;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 * Create the user
	 * @param User $user
	 */
	public function createUser(User &$user) {
		try {
			$sql  = "INSERT INTO USER (FIRST_NAME,LAST_NAME,AUTH_PROVIDER,AUTH_ID,EMAIL_ADDRESS,SUPER_USER)
					VALUES
					(:fname,:lname,:authProv,:authId,:email,:su)";
			$values=array();
			$values[":fname"]=$user->firstName;
			$values[":lname"]=$user->lastName;
			$values[":authProv"]=$user->authProvider;
			$values[":authId"]=$user->authId;
			$values[":email"]=$user->email;
			$values[":su"]=($user->superUser?"Y":"N");
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
			$user->id=$this->db->lastInsertId();

		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param User $user
	 */
	public function updateUser(User &$user) {
		try {
			$sql  = "UPDATE USER SET
					FIRST_NAME=:fname,
					LAST_NAME=:lname,
					AUTH_PROVIDER=:authProv,
					AUTH_ID=:authId,
					EMAIL_ADDRESS=:email,
					SUPER_USER=:su
					WHERE USER_ID=:uid";
			$values=array();
			$values[":fname"]=$user->firstName;
			$values[":lname"]=$user->lastName;
			$values[":authProv"]=$user->authProvider;
			$values[":authId"]=$user->authId;
			$values[":email"]=$user->email;
			$values[":su"]=($user->superUser?"Y":"N");
			$values[":uid"]=$user->id;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param int $id
	 */
	public function removeUser($id) {
		try {
			$sql  = "DELETE FROM USER WHERE USER_ID=:uid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":uid"=>$id));

		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param User $user
	 * @param array $row
	 */
	private function populateUser(User &$user,&$row,$withGames=false) {
		try {
			$user->firstName=$row["FIRST_NAME"];
			$user->lastName=$row["LAST_NAME"];
			$user->authProvider=$row["AUTH_PROVIDER"];
			$user->authId=$row["AUTH_ID"];
			$user->email=$row["EMAIL_ADDRESS"];
			$user->superUser=($row["SUPER_USER"]=="Y"?true:false);
			$user->id=$row["USER_ID"];
			if ($withGames) {
			$user->games=$this->retrieveGamesForUser($user->id);
			}
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param Game $game
	 */
	public function createGame(Game &$game) {
		try {
			$sql  = "INSERT INTO GAME (NAME,SYNOPSIS,DESCRIPTION,PRIVATE_FLAG,CREATED_BY,SOURCE,RULES,CANVAS,MIN_PLAYERS,MAX_PLAYERS,VERSION)
					VALUES
					(:name,:synopsis,:desc,:private,:uid,:source,:rules,:canvas,:min,:max,:version)";
			$values=array();
			$values[":name"]=$game->name;
			$values[":synopsis"]=$game->synopsis;
			$values[":desc"]=$game->description;
			$values[":private"]=($game->private?"Y":"N");
			$values[":uid"]=$game->createdBy->id;
			$values[":source"]=$game->source;
			$values[":rules"]=$game->rules;
			$values[":canvas"]=$game->canvas;
			$values[":min"]=$game->minPlayers;
			$values[":max"]=$game->maxPlayers;
			$values[":version"]=$game->version;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
			$game->id=$this->db->lastInsertId();
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));

		}
	}
	/**
	 *
	 * @param Game $game
	 */
	public function updateGame(Game &$game) {
		try {
			$sql  = "UPDATE GAME SET
					NAME=:name,
					SYNOPSIS=:synopsis,
					DESCRIPTION=:desc,
					PRIVATE_FLAG=:private,
					SOURCE=:source,
					RULES=:rules,
					CANVAS=:canvas,
					MIN_PLAYERS=:min,
					MAX_PLAYERS=:max,
					VERSION=:version
					WHERE GAME_ID=:gid";
			$values=array();
			$values[":name"]=$game->name;
			$values[":synopsis"]=$game->synopsis;
			$values[":desc"]=$game->description;
			$values[":private"]=($game->private?"Y":"N");
			$values[":source"]=$game->source;
			$values[":rules"]=$game->rules;
			$values[":canvas"]=$game->canvas;
			$values[":min"]=$game->minPlayers;
			$values[":max"]=$game->maxPlayers;
			$values[":version"]=$game->version;
			$values[":gid"]=$game->id;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);		;
		} catch (Exception $e) {
			print_r($e);
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param int $id
	 */
	public function removeGame($id) {
		try {
			$sql  = "DELETE FROM GAME WHERE GAME_ID=:gid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":gid"=>$id));
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param int $id
	 * @return Game
	 */
	public function retrieveGame($id) {
		try {
			$sql  = "SELECT G.*,U.* FROM GAME G INNER JOIN USER U ON U.USER_ID=G.CREATED_BY WHERE G.GAME_ID=:gid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":gid"=>$id));
			$row=$stmt->fetch(PDO::FETCH_ASSOC);
			$game=new Game();
			$this->populateGame($game, $row);
			return $game;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param int $id
	 * @return Game
	 */
	public function retrieveGameSource($id) {
		try {
			$sql  = "SELECT G.SOURCE FROM GAME G WHERE G.GAME_ID=:gid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":gid"=>$id));
			$row=$stmt->fetch(PDO::FETCH_ASSOC);
			return $row["SOURCE"];
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 * @return Array(Game)
	 */
	public function retrieveGames($fields) {
		try {
			$sql  = "SELECT * FROM GAME ORDER BY NAME";
			$stmt=$this->db->prepare($sql);
			$stmt->execute();
			$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
			$games=array();
			foreach ($data as $row) {
				$game=new Game();
				$this->populateGame($game, $row,$fields);
				array_push($games, $game);
			}
			return $games;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param int $userId
	 * @param allData bool - Retrieve all game data?
	 * @return Array(Game)
	 */
	public function retrieveGamesForUser($userId,$allData=true) {
		try {
			$sql  = "SELECT * FROM GAME WHERE CREATED_BY=:uid ORDER BY NAME";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":uid"=>$userId));
			$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
			$games=array();
			foreach ($data as $row) {
				$game=new Game();
				$this->populateGame($game, $row);
				array_push($games, $game);
			}
			return $games;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param Game $game
	 * @param array $row
	 * @param bool allData - Retrieve all data?
	 */
	private function populateGame(Game &$game,$row,$fields) {
		try {
			foreach ($this->gameColumnMap() as $field=>$column) {
				if (empty($fields) || in_array($field, $fields)) {
					$game->$field=$row[$column];
				}
			}
			if (empty($fields) || in_array("createdBy", $fields)) {
				$user=new User();
				$this->populateUser($user, $row);
				$game->createdBy=$user;
			}
			if (empty($fields) || in_array("options", $fields)) {
				$game->options=$this->retrieveOptionsForGame($game->id);
			}
			if (empty($fields) || in_array("scenarios", $fields)) {
				$game->scenarios=$this->retrieveScenariosForGame($game->id);
			}

		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}


	/**
	 *
	 * @param Option $option
	 */
	public function createOption(Option &$option) {
		try {

			$sql = "INSERT INTO GAME_OPTION (GAME_ID,LABEL,TYPE,DEFAULT_VALUE)
					VALUES (:oid,:label,:type,:def)";
			$values=array();
			$values[":oid"]=$option->gameId;
			$values[":label"]=$option->label;
			$values[":type"]=$option->type;
			$values[":def"]=$option->defaultValue;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
			$option->id=$this->db->lastInsertId();
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param Option $option
	 */
	public function updateOption(Option &$option) {
		try {

			$sql = "UPDATE GAME_OPTION
					SET
					GAME_ID=:oid,
					LABEL=:label,
					TYPE=:type,
					DEFAULT_VALUE=:def
					WHERE OPTION_ID=:optId";
			$values=array();
			$values[":oid"]=$option->gameId;
			$values[":label"]=$option->label;
			$values[":type"]=$option->type;
			$values[":def"]=$option->defaultValue;
			$values[":optId"]=$option->id;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $id
	 */
	public function removeOption($id) {
		try {
			$sql  = "DELETE FROM GAME_OPTION WHERE OPTION_ID=:id";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$id));
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param Option $option
	 * @param array $row
	 */
	private function populateOption(Option &$option,&$row) {
		$option->defaultValue=$row["DEFAULT_VALUE"];
		$option->id=$row["OPTION_ID"];
		$option->label=$row["LABEL"];
		$option->gameId=$row["GAME_ID"];
		$option->type=$row["TYPE"];
	}
	/**
	 *
	 * @param int $id
	 * @return Option
	 */
	public function retrieveOption($optionId) {
		try {

			$sql  = "SELECT O.* FROM GAME_OPTION O WHERE OPTION_ID=:oid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":oid"=>$optionId));
			$row=$stmt->fetch(pdo::FETCH_ASSOC);
			$option=new Option();
			$this->populateOption($option, $row);
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $gameId
	 * @return Array(Player)
	 */
	public function retrieveOptionsForGame($gameId) {
		try {
			$sql = "SELECT * FROM `GAME_OPTION` WHERE GAME_ID=:id ORDER BY LABEL";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$gameId));
			$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
			$options=array();
			foreach ($data as $row) {
				$option=new Option();
				$this->populateOption($option, $row);;
				array_push($options,$option);
			}
			return $options;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}


	public function createScenario(Scenario &$scenario) {
		try {
			$sql = "INSERT INTO SCENARIO (GAME_ID,DESCRIPTION,SCENARIO_NAME,DATA)
					VALUES (:id,:desc,:name,:data)";
			$values=array();
			$values[":id"]=$scenario->gameId;
			$values[":desc"]=$scenario->description;
			$values[":name"]=$scenario->name;
			$values[":data"]=$scenario->data;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
			$scenario->id=$this->db->lastInsertId();
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}

	public function updateScenario(Scenario &$scenario) {
		try {
			$sql = "UPDATE SCENARIO
					SET
					DESCRIPTION=:desc,SCENARIO_NAME=:name,DATA=:data
					WHERE SCENARIO_ID=:id";
			$values=array();
			$values[":desc"]=$scenario->description;
			$values[":name"]=$scenario->name;
			$values[":data"]=$scenario->data;
			$values[":id"]=$scenario->id;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $id
	 */
	public function removeScenario($id) {
		try {
			$sql = "DELETE FROM SCENARIO WHERE SCENARIO_ID=:id";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$id));
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}

	private function populateScenario(Scenario &$scenario,&$row) {
		$scenario->id=$row["SCENARIO_ID"];
		$scenario->gameId=$row["GAME_ID"];
		$scenario->description=$row["DESCRIPTION"];
		$scenario->name=$row["SCENARIO_NAME"];
		$scenario->data=$row["DATA"];
	}

	public function retrieveScenario($id) {
		try {
			$sql  = "SELECT * FROM SCENARIO WHERE SCENARIO_ID=:id";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$id));
			$row=$stmt->fetch(pdo::FETCH_ASSOC);
			$scenario=new Scenario();
			$this->populateScenario($scenario, $row);
			return $scenario;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}

	public function retrieveScenariosForGame($gameId) {
		try {
			$sql  = "SELECT * FROM SCENARIO WHERE GAME_ID=:id";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$gameId));
			$data=$stmt->fetch(pdo::FETCH_ASSOC);
			$scenarios=array();
			foreach ($data as $row) {
				$scenario=new Scenario();
				$this->populateScenario($scenario, $row);
				array_push($scenarios,$scenario);
			}
			return $scenarios;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}


	/**
	 *
	 * @param Player $player
	 */
	public function createPlayer(Player &$player) {
		try {
				$sql  = "INSERT INTO PLAYER (GAME_ID,CREATED_BY,PLAYER_NAME,PLAYER_DESCRIPTION,PLAYER_VERSION,SOURCE,UPDATED_ON)
						VALUES (:gid,:uid,:name,:desc,:version,:source,:updatedOn)";
				$values=array();
				$values[":gid"]=$player->gameId;
				$values[":uid"]=$player->owner->id;
				$values[":name"]=$player->name;
				$values[":desc"]=$player->description;
				$values[":version"]=$player->version;
				$values[":source"]=$player->source;
				//$values["templateFlag"]=$player->defaultTemplateFlag;
				//$values["opponentFlag"]=$player->defaultOpponentFlag;
				//$values["testFlag"]=$player->testOpponentFlag;
				$values["updatedOn"]=date("Y-m-d H:i:s");
				$stmt = $this->db->prepare($sql);
				$stmt->execute($values);
				$player->id=$this->db->lastInsertId();
				$player->updatedOn=date("Y-m-d H:i:s");
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param Player $player
	 */
	public function updatePlayer(Player &$player) {
			try {
				$sql  = "UPDATE PLAYER SET
						PLAYER_NAME=:name,
						PLAYER_DESCRIPTION=:desc,
						PLAYER_VERSION=:version,
						SOURCE=:source ".//",
						//DEFAULT_TEMPLATE_FLAG=:templateFlag,
						//DEFAULT_OPPONENT_FLAG=:opponentFlag,
						//TEST_OPPONENT_FLAG=:testFlag
						"WHERE PLAYER_ID=:id";
				$values=array();
				$values[":name"]=$player->name;
				$values[":desc"]=$player->description;
				$values[":version"]=$player->version;
				$values[":source"]=$player->source;
				$values[":id"]=$player->id;
//				$values["templateFlag"]=$player->defaultTemplateFlag;
//				$values["opponentFlag"]=$player->defaultOpponentFlag;
//				$values["testFlag"]=$player->testOpponentFlag;
				$stmt = $this->db->prepare($sql);
				$stmt->execute($values);
				$player->updatedOn=date("Y-m-d H:i:s");
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $id
	 */
	public function removePlayer($id) {
		try {
			$sql  = "DELETE FROM PLAYER WHERE PLAYER_ID=:id";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array(":id"=>$id));
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}


	/**
	 *
	 * @param Player $player
	 * @param array $row
	 */
	private function populatePlayer(Player &$player,&$row,$fields) {
		foreach ($this->playerColumnMap() as $field=>$column) {
			if (empty($fields) || in_array($field, $fields)) {
				$player->$field=$row[$column];
			}
		}
		if (empty($fields) || in_array("owner",$fields)) {
			$user = new User();
			$this->populateUser($user, $row);
			$player->owner=$user;
		}
	}
	/**
	 *
	 * @param int $id
	 * @return Player
	 */
	public function retrievePlayer($id) {
		try {
			$sql  = "SELECT P.*,U.* FROM PLAYER P INNER JOIN USER U ON U.USER_ID=P.CREATED_BY WHERE P.PLAYER_ID=:id";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$id));
			$row=$stmt->fetch(pdo::FETCH_ASSOC);
			$player=new Player();
			$this->populatePlayer($player, $row);
			return $player;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	public function retrieveDefaultTemplate($gameId) {
		try {
			$sql  = "SELECT P.*,U.* FROM PLAYER P INNER JOIN USER U ON U.USER_ID=P.CREATED_BY WHERE P.GAME_ID=:id AND P.DEFAULT_TEMPLATE_FLAG!='N'";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$gameId));
			$row=$stmt->fetch(pdo::FETCH_ASSOC);
			$player=new Player();
			$this->populatePlayer($player, $row);
			return $player;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $id
	 */
	public function retrievePlayerSource($id) {
		try {
			$sql  = "SELECT P.PUBLISHED_SOURCE FROM PLAYER P WHERE P.PLAYER_ID=:id";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$id));
			$row=$stmt->fetch(pdo::FETCH_ASSOC);
			return $row['PUBLISHED_SOURCE'];
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $gameId
	 * @return Array(Player)
	 */
	public function retrievePlayersForGame($gameId,$publishedOnly=false) {
		try {
			$sql  = "SELECT P.*,U.* FROM PLAYER P INNER JOIN USER U ON U.USER_ID=P.CREATED_BY WHERE P.GAME_ID=:id ";
			if ($publishedOnly) {
				$sql .= " AND P.PUBLISHED_SOURCE IS NOT NULL ";
			}
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$gameId));
			$data=$stmt->fetchAll(pdo::FETCH_ASSOC);
			$players=array();
			foreach ($data as $row) {
				$player=new Player();
				$this->populatePlayer($player, $row);
				// Set a link to itself
				$player->href = "/server/service/players/".$player->id;
				array_push($players,$player);
			}
			return $players;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $userId
	 * @param int $gameId
	 * @return array(Player)
	 */
	public function retrievePlayersForUserAndGame($userId,$gameId) {
		try {
			$sql  = "SELECT P.*,U.* FROM PLAYER P INNER JOIN USER U ON U.USER_ID=P.CREATED_BY WHERE P.GAME_ID=:id AND P.CREATED_BY=:uid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$gameId,":uid"=>$userId));
			$data=$stmt->fetchAll(pdo::FETCH_ASSOC);
			$players=array();
			foreach ($data as $row) {
				$player=new Player();
				$this->populatePlayer($player, $row);
				// Set a link to itself
				$player->href = "/server/service/players/".$player->id;
				array_push($players,$player);
			}
			return $players;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
		}
	}
	/**
	 *
	 * @param int $userId
	 * @return array(Player)
	 */
	public function retrievePlayersForUser($userId) {
		try {
			$sql  = "SELECT P.*,U.* FROM PLAYER P INNER JOIN USER U ON U.USER_ID=P.CREATED_BY WHERE P.CREATED_BY=:uid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":uid"=>$userId));
			$data=$stmt->fetch(pdo::FETCH_ASSOC);
			$players=array();
			foreach ($data as $row) {
				$player=new Player();
				$this->populatePlayer($player, $row);
				array_push($players,$player);
			}
			return $players;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
		}
	}

	public function publishPlayer($playerId) {
		try {
			$sql  = "UPDATE PLAYER SET PUBLISHED_SOURCE=SOURCE, PUBLISHED_ON=:now WHERE PLAYER_ID=:pid";
			$values=array();
			$values[":now"]=date("Y-m-d H:i:s");
			$values[":pid"]=$playerId;
			$stmt = $this->db->prepare($sql);
			$stmt->execute($values);
			return $values[":now"];
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
		}
	}


	/** NOTE: VERSIONS ARE STORED AS GAMES FOR NOW.  KEEPING THIS CODE IN CASE THAT NEEDS TO BE CHANGED */
	/**
	 *
	 * @param Version $version
	 */
	public function createGameVersion(Version &$version) {
		try {
			$sql  = "INSERT INTO VERSION (VERSION_NAME,GAME_ID,SOURCE,DESCRIPTION,RULES,CANVAS)
					VALUES (:name,:gid,:source,:desc,:rules,:canvas)";
			$values=array();
			$values[":name"]=$version->versionName;
			$values[":gid"]=$version->gameId;
			$values[":source"]=$version->source;
			$values[":desc"]=$version->description;
			$values[":rules"]=$version->rules;
			$values[":canvas"]=$version->canvas;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
			$version->versionId=$this->db->lastInsertId();
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw new Exception("Database error occurred at ".date("Ymd H:i:s"));
		}
	}
	/**
	 *
	 * @param Version $version
	 */
	public function updateGameVersion(Version &$version) {
		try {
			$sql  = "UPDATE VERSION SET
					VERSION_NAME=:name,
					GAME_ID=:gid,
					SOURCE=:source,
					DESCRIPTION=:desc,
					RULES=:rules,
					CANVAS=:canvas
					WHERE VERSION_ID=:vid";
			$values=array();
			$values[":name"]=$version->versionName;
			$values[":gid"]=$version->gameId;
			$values[":source"]=$version->source;
			$values[":desc"]=$version->description;
			$values[":rules"]=$version->rules;
			$values[":canvas"]=$version->canvas;
			$values[":vid"]=$version->versionId;
			$stmt=$this->db->prepare($sql);
			$stmt->execute($values);
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $id
	 */
	public function removeGameVersion($id) {
		try {
			$sql  = "DELETE FROM VERSION WHERE VERSION_ID=:vid";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":vid"=>$id));
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param Version $version
	 * @param int $row
	 */
	private function populateGameVersion(Version &$version,$row) {
		try {
			$version->canvas=$row["CANVAS"];
			$version->description=$row["DESCRIPTION"];
			$version->gameId=$row["GAME_ID"];
			$version->rules=$row["RULES"];
			$version->source=$row["SOURCE"];
			$version->versionId=$row["VERSION_ID"];
			$version->versionName=$row["VERSION_NAME"];
			$version->options=$this->retrieveOptionsForVersion($version->versionId);
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;

		}
	}
	/**
	 *
	 * @param int $id
	 * @return Version
	 */
	public function retrieveGameVersion($id) {
		try {
			$sql = "SELECT * FROM VERSION WHERE VERSION_ID=:id";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$id));
			$row=$stmt->fetch(PDO::FETCH_ASSOC);
			$version=new Version();
			$this->populateGameVersion($version, $row);
			return $version;

		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
		}
	}
	/**
	 *
	 * @param int $gameId
	 * @return Array(Version)
	 */
	public function retrieveVersionsForGame($gameId) {
		try {
			$sql = "SELECT * FROM `VERSION` WHERE GAME_ID=:id ORDER BY VERSION_ID";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$gameId));
			$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
			$versions=array();
			foreach ($data as $row) {
				$version=new Version();
				$this->populateGameVersion($version, $row);
				array_push($versions,$version);
			}
			return $versions;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/**
	 *
	 * @param int $versionId
	 * @return Array(Player)
	 */
	public function retrieveOptionsForVersion($versionId) {
		try {
			$sql = "SELECT * FROM `VERSION_OPTION` WHERE GAME_ID=:id ORDER BY LABEL";
			$stmt=$this->db->prepare($sql);
			$stmt->execute(array(":id"=>$versionId));
			$data=$stmt->fetchAll(PDO::FETCH_ASSOC);
			$options=array();
			foreach ($data as $row) {
				$option=new Option();
				$this->populateOption($option, $row);;
				array_push($options,$option);
			}
			return $options;
		} catch (Exception $e) {
			Logger::log("DB Error occurred: ".$e->getMessage());
			throw $e;
		}
	}
	/*EOF VERSION METHODS*/
}
?>