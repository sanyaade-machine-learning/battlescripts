<?php

require_once $_SERVER["DOCUMENT_ROOT"].'/server/UtilityFunctions.php';
try {
	$db = DataBroker::singleton();
	echo "Successful connection<br/>";
} catch (Exception $ex) {
	echo "Unable to establish a connection. Error: ".$ex->getMessage();
	exit;
}
$broker = new BattleBroker();
try {
	$user=new User();
	$user->authId="1234";
	$user->authProvider="google";
	$user->email="user@here.com";
	$user->firstName="First";
	$user->lastName="Last";
	$user->superUser=true;
	$broker->createUser($user);
	echo "User created<br/>";
} catch (Exception $e) {
	echo "Unable to create user";
}

try {
	$ru=$broker->retrieveUser($user->id);
	echo "user retrieved<br/>";
} catch (Exception $e) {
	echo "Unable to retrieve user";
	exit;
}
//echo json_encode($ru);
try {
	$ru->firstName="NewName_".$ru->id;
	$broker->updateUser($ru);
	echo "user updated<br/>";
} catch (Exception $ex) {
	echo "Unable to update";
	exit;
}
$game = new Game();
$game->createdBy=$ru;
$game->description="game description here";
$game->name="Game Name";
$game->synopsis="Synopsis";
$game->private=false;
$game->canvas="canvas here";
$game->maxPlayers=5;
$game->minPlayers=1;
$game->rules="rules";
$game->source="source";

try {
	echo "creating game<br/>";
	$broker->createGame($game);
	echo "Game created<br/>";
} catch (Exception $e) {
	echo "Unable to create game";
	exit;
}

$game->name="New game name";
try {
	echo "updating game<br/>";
	$broker->updateGame($game);
	echo "Game updated<br/>";
} catch (Exception $e) {
	echo "Unable to create game";
	exit;
}
try {
	echo "retrieving all games<br/>";
	$games=$broker->retrieveGames();
	foreach ($games as $tgame) {
		echo "Retrieved game ".$tgame->id."<br/>";
	}

} catch (Exception $e) {
	echo "Unable to retrieve games";
	exit;
}
try {
	echo "retrieving games for user<br/>";
	$games=$broker->retrieveGamesForUser($ru->id);
	foreach ($games as $tgame) {
		echo "Retrieved game ".$tgame->id."<br/>";
	}

} catch (Exception $e) {
	echo "Unable to retrieve games";
	exit;
}

$scenario = new Scenario();
$scenario->data="data here";
$scenario->description="scenario desc";
$scenario->gameId=$game->id;
$scenario->name="First scenario";

try {
	echo "Creating scenario<br/>";
	$broker->createScenario($scenario);
	echo "scenario created<br/>";
} catch (Exception $e) {
	echo "Unable to create scenario";
	print_r($e);
	exit;
}
$scenario->name="new First scenario";
try {
	$broker->updateScenario($scenario);
	echo "scenario updated<br/>";
} catch (Exception $e) {
	echo "Unable to update scenario";
	print_r($e);
	exit;
}

try {
	$rv=$broker->retrieveScenario($scenario->id);
	echo "scenario retrieved<br/>";
} catch (Exception $e) {
	echo "Unable to retrieve scenario";
	print_r($e);
	exit;
}
try {
	echo "retrieving all game scenarios<br/>";
	$versions=$broker->retrieveScenariosForGame($game->id);
	foreach ($versions as $tver) {
		echo "Retrieved scenario ".$tver->id."<br/>";
	}

} catch (Exception $e) {
	echo "Unable to retrieve secenarios";
	exit;
}
$option = new Option();
$option->gameId=$game->id;
$option->label="Number tracks";
$option->defaultValue=2;
$option->type="NUMBER";
try {
	echo "creating option<br/>";
	$broker->createOption($option);
	echo "option created<br/>";
} catch (Exception $e) {
	echo "Unable to create option";
	exit;
}
$option->defaultValue=3;
try {
	echo "updating option<br/>";
	$broker->updateOption($option);
	echo "option updated<br/>";
} catch (Exception $e) {
	echo "Unable to update option";
	exit;
}
try {
	echo "retrieve option<br/>";
	$ro=$broker->retrieveOption($option->id);
	echo "option retrieved<br/>";
} catch (Exception $e) {
	echo "Unable to retrieve option";
	exit;
}
try {
	echo "retrieve options for game<br/>";
	$gos=$broker->retrieveOptionsForGame($game->id);
	foreach ($gos as $go) {
		echo "option for game retrieved<br/>";
	}
	echo "options retrieved<br/>";
} catch (Exception $e) {
	echo "Unable to retrieve options";
	exit;
}


$player = new Player();
$player->owner=$ru;
$player->description="plyr description here";
$player->name="Player Name";
$player->source="Player SOURCE";
$player->gameId=$game->id;
$player->version="1.1.1.1";


try {
	echo "creating player<br/>";
	$broker->createPlayer($player);
	echo "Player created<br/>";
} catch (Exception $e) {
	echo "Unable to create player";
	exit;
}

$player->name="New player name";
try {
	echo "updating player<br/>";
	$broker->updatePlayer($player);
	echo "player updated<br/>";
} catch (Exception $e) {
	echo "Unable to create player";
	exit;
}
try {
	echo "retrieving all players<br/>";
	$players=$broker->retrievePlayersForGame($game->id);
	foreach ($players as $tgame) {
		echo "Retrieved player ".$tgame->id."<br/>";
	}

} catch (Exception $e) {
	echo "Unable to retrieve players";
	exit;
}
try {
	echo "retrieving players for user<br/>";
	$players=$broker->retrievePlayersForUser($ru->id);
	foreach ($players as $tgame) {
		echo "Retrieved player ".$tgame->id."<br/>";
	}

} catch (Exception $e) {
	echo "Unable to retrieve players";
	exit;
}
try {
	echo "retrieving players for user and game<br/>";
	$players=$broker->retrievePlayersForUserAndGame($ru->id,$game->id);
	foreach ($players as $tgame) {
		echo "Retrieved player ".$tgame->id."<br/>";
	}

} catch (Exception $e) {
	echo "Unable to retrieve players";
	exit;
}


$allGames=$broker->retrieveGames();
echo json_encode($allGames);
?>