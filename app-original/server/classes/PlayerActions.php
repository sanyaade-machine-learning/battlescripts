<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class PlayerActions {
	protected  $broker; //DataBroker

	function __construct() {
		$this->broker=new BattleBroker();
	}
	public function retrievePlayersForGame(Application $app, Request $request,$id) {
		try {
			$fields=null;
			if ($request->query->get("fields")) {
				$fields=explode(",", $request->query->get("fields"));
			}
			$players = $this->broker->retrievePlayersForGame($id,true);

			if (!empty($fields)) {
				$partials=array();
				foreach ($players as $player) {
					$partial=new stdClass();
					foreach ($fields as $field) {
						$partial->$field=$player->$field;
					}
					array_push($partials,$partial);
				}
				$players=$partials;
			}

		} catch (Exception $ex) {
			//TODO: Log
			$app->abort(500);
		}
		return json_encode($players);
	}
	public function retrieveMyPlayers(Application $app, Request $request, $id) {
		if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1) {
			$app->abort(401,"This action requires you to be authenticated");
		}
		try {

			$players = $this->broker->retrievePlayersForUserAndGame($_SESSION['user']['id'],$id);
			if ($request->query->get("fields")) {
				$fields=explode(",", $request->query->get("fields"));
				$partials=array();
				foreach ($players as $player) {
					$partial=new stdClass();
					foreach ($fields as $field) {
						$partial->$field=$player->$field;
					}
					array_push($partials,$partial);
				}
				$players=$partials;
			}
		} catch (Exception $ex) {
			//TODO: Log
			$app->abort(500);
		}
		return json_encode($players);
	}
	public function createPlayer(Application $app,Request $request) {
		if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1) {
			$app->abort(401,"This action requires you to be authenticated");
		}
		try {
			$player = new Player();
			$player->name=$request->request->get("name");
			$player->description=$request->request->get("description");
			$player->source=$request->request->get("source");
			$player->version=$request->request->get("version");
			$player->gameId=$request->request->get("gameId");
//			$player->defaultOpponentFlag=$request->request->get("defaultOpponentFlag") || "N";
//			$player->defaultTemplateFlag=$request->request->get("defaultTemplateFlag") || "N";
//			$player->testOpponentFlag=$request->request->get("testOpponentFlag") || "N";
			$user = new User();
			$user->id=$_SESSION["user"]["id"];
			$player->owner=$user;
			$this->broker->createPlayer($player);
		} catch (Exception $ex) {
			//TODO: Log
			$app->abort(500,"Error creating player:".$ex->getMessage());
		}
		return json_encode($player);
	}
	public function retrievePlayer(Application $app,Request $request,$playerId) {
		try {
			$player=$this->broker->retrievePlayer($playerId);
			if ($request->query->get("fields")) {
				$fields=explode(",", $request->query->get("fields"));
				$partial=new stdClass();
				foreach ($fields as $field) {
					$partial->$field=$player->$field;
				}
				$player=$partial;
			}
		} catch (Exception $ex) {
			//TODO: Log
			$app->abort(500);
		}
		return json_encode($player);
	}
	public function retrievePlayerSource(Application $app, $id) {
		try {
			$source = $this->broker->retrievePlayerSource($id);
			$lodash = file_get_contents("../../js/lodash.js");
			$api = file_get_contents("../../js/player-api.js");
			return new Response($api."\n".$lodash."\n".$source, 200, array('Content-type' => 'text/javascript'));
		} catch (Exception $ex) {
			$app->abort(500);
		}
	}
	public function updatePlayer(Application $app,Request $request,$playerId) {
		if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1) {
			$app->abort(401,"This action requires you to be authenticated");
		}
		try{
			$player=$this->broker->retrievePlayer($playerId);
			if ($player->owner->id != $_SESSION["user"]["id"]) {
				$app->abort(403,"You are not authorized to perform this action");
			}
			$player->name=$request->request->get("name");
			$player->description=$request->request->get("description");
			$player->source=$request->request->get("source");
			$player->version=$request->request->get("version");
//			$player->defaultOpponentFlag=$request->request->get("defaultOpponentFlag") || "N";
//			$player->defaultTemplateFlag=$request->request->get("defaultTemplateFlag") || "N";
//			$player->testOpponentFlag=$request->request->get("testOpponentFlag") || "N";
			$this->broker->updatePlayer($player);
		} catch (Exception $ex) {
			//TODO: Log
			$app->abort(500,"Error saving player:".$ex->getMessage());
		}
		return json_encode($player);
	}
	public function removePlayer(Application $app,Request $request,$playerId) {
		if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1) {
			$app->abort(401,"This action requires you to be authenticated");
		}
		try{
			$player=$this->broker->retrievePlayer($playerId);
			if ($player->owner->id != $_SESSION["user"]["id"]) {
				$app->abort(403,"You are not authorized to perform this action");
			}
			$this->broker->removePlayer($playerId);
		} catch (Exception $ex) {
			//TODO: Log
			$app->abort(500,"Error deleting player:".$ex->getMessage());
		}
		return json_encode(array("status"=>"success"));
	}
	public function publishPlayer(Application $app,Request $request,$playerId) {
		if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1) {
			$app->abort(401,"This action requires you to be authenticated");
		}
		try{
			$player=$this->broker->retrievePlayer($playerId);
			if ($player->owner->id != $_SESSION["user"]["id"]) {
				$app->abort(403,"You are not authorized to perform this action");
			}
			$published_on = $this->broker->publishPlayer($playerId);
		} catch (Exception $ex) {
			//TODO: Log
			$app->abort(500,"Error publishing player:".$ex->getMessage());
		}
		return json_encode(array("status"=>"success","published_on"=>$published_on));
	}
}
?>