<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class GameActions {
	protected  $broker; //DataBroker
	
	function __construct() {
		$this->broker=new BattleBroker();
	}
	function retrieveGames(Application $app,Request $request) {
		try {
			$fields=null;
			if ($request->query->get("fields")) {
				$fields=explode(",", $request->query->get("fields"));
			}
			$games = $this->broker->retrieveGames($fields);
	
			if (!empty($fields)) {
				$partials=array();
				foreach ($games as $game) {
					$partial=new stdClass();
					foreach ($fields as $field) {
						$partial->$field=$game->$field;
					}
					array_push($partials,$partial);
				}
				$games=$partials;
			}
		} catch (Exception $ex) {
			//TODO: Log
			$app->abort(500);
		}
	
		return(json_encode($games));
	}
	
	public function retrieveGame(Application $app, Request $request,$id){
			try {
        		$game = $this->broker->retrieveGame($id);
        		if(isset($game->rules))
        			$game->rules = $this->parseMarkdown($game->rules);
        		
        		if ($request->query->get("fields")) {
        			$fields=explode(",", $request->query->get("fields"));
        			$partial=new stdClass();
        			foreach ($fields as $field) {
        				$partial->$field=$game->$field;
        			}
        			$game=$partial;
        		}        		
            } catch (Exception $ex) {
            	//TODO: Log
            	$app->abort(500);
            }
            return (json_encode($game));
        }

        public function createGame(Application $app,Request $request) {
        	if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1) {
        		//$app->abort(401,"Please login to continue");
        	}
        	try {
        		//TODO: Convert and fix
        		$game = new Game();
        		$game->canvas=$_POST["canvas"];
        		$game->description=$_POST["description"];
        		$game->maxPlayers=$_POST["maxPlayers"];
        		$game->minPlayers=$_POST["minPlayers"];
        		$game->name=$_POST["name"];
        		$game->source=$_POST["source"];
        		$game->rules=$_POST["rules"];
        		$game->synopsis=$_POST["synopsis"];
        		$game->version=$_POST["version"];
        		$user = new User();
        		$user->id=$_SESSION["user"]["id"];
        		$game->createdBy=$user;
        		$this->broker->createGame($game);
        		if (is_array($_POST["options"])) {
        			foreach ($_POST["options"] as $postOption) {
        				$option = new Option();
        				$option->defaultValue=$postOption["defaultValue"];
        				$option->label=$postOption["label"];
        				$option->type=$postOption["type"];
        				$option->gameId=$game->id;
        				$this->broker->createOption($option);
        				array_push($game->options,$option);
        			}
        		}
        		if (is_array($_POST["scenarios"])) {
        			foreach ($_POST["scenarios"] as $postScenario) {
        				$scenario = new Scenario();
        				$scenario->data=$postScenario["data"];
        				$scenario->description=$postScenario["description"];
        				$scenario->name=$postScenario["name"];
        				$scenario->gameId=$game->id;
        				$this->broker->createScenario($scenario);
        				array_push($game->scenarios,$scenario);
        			}
        		}
        	} catch (Exception $ex) {
        		//TODO: Log
        		$app->abort(500);
        	}
        
        	return(json_encode($game));
        }
        public function updateGame(Application $app,Request $request, $id) {
        	if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1) {
        		$app->abort(401,"Please login to continue");
        	}
        	try {
        		//TODO: Update to use request and fix null save issue
        		$game = $this->broker->retrieveGame($id);
        		$game->id=$id;
        		$game->canvas=$_POST["canvas"];
        		$game->description=$_POST["description"];
        		$game->maxPlayer=$_POST["maxPlayer"];
        		$game->minPlayer=$_POST["minPlayer"];
        		$game->name=$_POST["name"];
        		$game->source=$_POST["source"];
        		$game->rules=$_POST["rules"];
        		$game->synopsis=$_POST["synopsis"];
        		$game->version=$_POST["version"];
        		$this->broker->updateGame($game);
        		//TODO: Fix the option/scenario update
        		if (is_array($_POST["options"])) {
        			foreach ($_POST["options"] as $postOption) {
        				$option = new Option();
        				$option->defaultValue=$postOption["defaultValue"];
        				$option->label=$postOption["label"];
        				$option->type=$postOption["type"];
        				$option->gameId=$game->id;
        				$this->broker->createOption($option);
        				array_push($game->options,$option);
        			}
        		}
        		if (is_array($_POST["scenarios"])) {
        			foreach ($_POST["scenarios"] as $postScenario) {
        				$scenario = new Scenario();
        				$scenario->data=$postScenario["data"];
        				$scenario->description=$postScenario["description"];
        				$scenario->name=$postScenario["name"];
        				$scenario->gameId=$game->id;
        				$this->broker->createScenario($scenario);
        				array_push($game->scenarios,$scenario);
        			}
        		}
        	} catch (Exception $ex) {
        		//TODO: Log
        		$app->abort(500);
        	}
        
        	return(json_encode($game));
        }  
        public function retrieveGameSource(Application $app, $id) {
        	try {
        		$source = $this->broker->retrieveGameSource($id);
        		$lodash = file_get_contents("../../js/lodash.js");
        		$api = file_get_contents("../../js/game-api.js");
        		return new Response($lodash."\n".$source."\n".$api, 200, array('Content-type' => 'text/javascript'));
        	} catch (Exception $ex) {
        		$app->abort(500);
        	}
        } 
        public function retrieveTemplateSource(Application $app,Request $request,$id) {
        	try {
        		$player=$this->broker->retrieveDefaultTemplate($id);
        	} catch (Exception $ex) {
        		//TODO: Log
        		$app->abort(500);
        	}
        	if ($player && !empty($player->publishedSource)) {
        		return $player->publishedSource;
        	} else {
        		return "";
        	}
        	
        }
        public function parseMarkdown($text){
        	require_once __DIR__.'/../../parsedown/Parsedown.php';
        	$parseDown = new Parsedown();
        	return $parseDown->parse($text);
        }                     
}
?>