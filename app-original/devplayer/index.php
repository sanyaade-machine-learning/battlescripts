<!DOCTYPE html>
<?php
$userid = -1;
$logged_in = false;
if(isset($_SESSION['user']) && $_SESSION['user']['id']>0) {
	$userid = $_SESSION['user']['id'];
	$logged_in = true;
}
?>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <title>Battle Scripts</title>

  <script type="text/javascript" src="../js/jquery.js"></script>
  <script type="text/javascript" src="../js/lodash.js"></script>
  <script type="text/javascript" src="../js/angular.js"></script>
  <script type="text/javascript" src="../js/angular-resource.js"></script>
  <script type="text/javascript" src="../js/restangular.js"></script>
  <script type="text/javascript" src="../js/battlescripts-app.js"></script>
  <script type="text/javascript" src="../js/Arbiter.js"></script>
  <script type="text/javascript" src="../js/Match.js"></script>
  <script type="text/javascript" src="../js/modalLogin.js"></script>
  <script type="text/javascript" src="../js/hello.min.js"></script>
  <script type="text/javascript" src="../lib/codemirror-3.20/lib/codemirror.js"></script>
  <script type="text/javascript" src="../lib/codemirror-3.20/mode/javascript/javascript.js"></script>
  <!--<script type="text/javascript" src="../js/jquery.tooltipster.js"></script>-->
  <script type="text/javascript" src="devplayer.js"></script>

  <link rel="stylesheet" type="text/css" href="../css/reset.css"/>
  <link rel="stylesheet" type="text/css" href="../css/devplayer.css"/>
  <link rel="stylesheet" href="../lib/codemirror-3.20/lib/codemirror.css" />
  <link rel="stylesheet" href="../lib/codemirror-3.20/theme/mdn-like.css" />
  <link rel="stylesheet" type="text/css" href="../css/tooltipster.css"/>
  <link href='//fonts.googleapis.com/css?family=Nixie+One|Ruda:400,900,700' rel='stylesheet' type='text/css'>

<script>
/*
$(function() {
	$('*[title]').tooltipster({
	   animation: 'grow',
	   delay: 350
	});
});
*/
</script>
</head>
<body>

<div id="wrap" class="wrap" ng-app="battlescripts" ng-controller="PlayerDeveloperController" ng-class="{ready:dev_game && player}">
    <div id="header">
       <h1>BattleScripts Player Developer</h1>
       <ul>
           <li>[<a href="/">home</a>] </li>
           <li>[<a href="/arena">arena</a>] </li>
           <li>[<a href="/contact.html">contact</a>] </li>
       </ul>
    </div>

    <div id="body" ng-class="{preload:(!dev_game||!player)}">
        <div id="left-nav" ng-class="{centered:(!dev_game||!player.id),locked:(player && !player.id),open:( !player || !player.id || left_nav_open)}">
            <div>
                <div ng-click="save()" id='save-player' class="persistant-button"><a>Save Player</a></div>
                <div ng-click="publish()" ng-show="player.id" id='publish-player' class="persistant-button" title="Changes must be published before they are visible to other players"><a>Publish Player</a></div>
                <div ng-show="player.id">
                	<div ng-click="show_advanced_options=!show_advanced_options" class="advanced_toggle" ng-class="{advanced_toggle_on:show_advanced_options}"></div>
                </div>
                <div ng-show="player.id && show_advanced_options" class="advanced_options">
                	<div title="Mark this player as unpublished so others cannot see it" ng-click="unpublish()" ng-show="player.id" id='unpublish-player' class="persistant-button"><a>Unpublish Player</a></div>
                	<div title="Revert the source in the editor to the previously published version" ng-click="revert()" ng-show="player.id" id='revert-player' class="persistant-button"><a>Revert to Published</a></div>
                	<div title="Delete this player" ng-click="delete()" ng-show="player.id" id='delete-player' class="persistant-button"><a>Delete Player</a></div>
                </div>
				<div>
					<div ng-hide="player" class="panel-group">
						To develop a player, first select a game and then a player to edit.
						<br>
						Or create a new player from scratch.					</div>
					<div class="panel-group">
						<label for="game-select">Select a Game: </label>
						<select ng-hide="games.length==0" ng-model="dev_game.id" ng-options="g.id as g.name for g in games" id="game-select"></select>
						<div ng-show="games.length==0" style="color:black;">Loading Game List...</div>
					</div>
					<div ng-show="dev_game">
						<div class="panel-group">
							<label for="player-id">Player: </label>
							<div ng-show="!player && myplayers===undefined" style="color:black;">Loading Player List...</div>
							<div ng-show="!player && myplayers && myplayers.length==0">You have no saved players for this game.<br>
				                <input type="button" value="Create A New Player" ng-click="create()">
              				</div>
							<div ng-show="player || (myplayers && myplayers.length>0)">
								<select id="player-id" ng-model="player" ng-options="p as p.name for p in myplayers"></select>
								<div ng-show="dev_game && !player" class="panel-group">
									or <input type="button" value="Create A New Player" ng-click="create()">
								</div>							</div>
							<div ng-show="!player && myplayers===null" class="center-text">You are not logged in.<br>You can <input type="button" value="Create" ng-click="create()"> a Player or <input type="button" value="Login" ng-click="login_my_players()"> to access your players.</div>
						</div>
						<div ng-show="player">
							<div class="panel-group"><label for='name-id'>Name: </label><input type='text' id="name-id" ng-model="player.name" ng-required/></div>
							<div class="panel-group"><label for='version-id'>Version: </label><input type='text' id="version-id" ng-model="player.version"/></div>
							<div class="panel-group"><label for='description-id'>Description: </label><textarea id="description-id" ng-model="player.description"></textarea></div>
<?php /*
							<div class="panel-group" title="Unpublished players are visible only to the player authors. If you want your player to be visible to others, you must publish it."><input type="checkbox" id="published-id" ng-model="player.published"> <label for='published-id'>Published for others to see</label></div>

							<div class="panel-group" title="When other users create players for this game, use this player's source as the default template them to start their player from'">
                            <h3> Game Developer Options: </h3>
                            <input type="checkbox" id="template-id" ng-model="player.template"> <label for='template-id'>Template Player for "Create New"</label></div>
							<div class="panel-group" title="When other users are testing their players in this game, use this player as the default opponent for them to play"><input type="checkbox" id="default-opponent-id" ng-model="player.default_opponent"> <label for='default-opponent-id'>Default Opponent</label></div>
*/ ?>
						</div>
					</div>
				</div>

            </div><div ng-show="dev_game&&player.id" id="panel-collapse" ng-click="toggle_left_nav()"></div>
        </div>
  <div class="content">
			<div id="message" ng-class="{error:message_error}" ng-show="message">{{message}}</div>
            <div class="panel_selector">
                Show:
                <div class="panel_button active" rel="panel_source">Source</div>
                <div class="panel_button" rel="panel_move_tester">Move Tester</div>
                <div class="panel_button active" rel="panel_game_runner">Game Runner</div>
                <div class="panel_button" rel="panel_console">Console</div>
                <div class="panel_button" rel="panel_game_info">Game API/Help</div>
				<div class="panel_button" rel="panel_help">Help</div>
            </div>
            <div class="panels">
                <div class="panel" id="panel_source">
                    <div class="panel_title">Source</div>
                    <div class="panel_body panel_source">
                        <textarea codemirror="conf" ng-model="player.source" id="source"></textarea>
                    </div>
                    <div id="compile_results" class="panel_footer {{compile_status}}">
                        {{compile_message}}                    </div>
                </div>
                <div class="panel" id="panel_move_tester">
                    <div class="panel_title">Move Tester</div>
                    <div class="panel_body">
						Paste a game JSON data structure into the top box and click Run to see what move your player will make with a given input.
						<br><br>
						Game JSON:<br>
                        <textarea ng-change="move_test_modified=true" Xtitle="The JSON structure that the game passes to the player when it requests a move" id="move_tester_input" ng-model="move_test_input"></textarea>
                        <br>
                        <input type="checkbox" ng-model="move_test_use_runner_data"> Copy Game data from the Game Runner
                        <div id="move_tester_run">
                            <span class="button" ng-click="test_move()">Call Player.move()</span>                        </div>
                        <div title="The move that your player returned from the given input" ng-show="move_test_output">
	                        Player move:<br><br>
                        	{{move_test_output}}                        </div>
                    </div>
                </div>
                <div class="panel" id="panel_game_runner">
                    <div class="panel_title">Game Runner</div>
                    <div class="panel_body">
                        <div id="game_controls">
                            Opponent: <select id="opponent" ng-model="opponent" ng-options="o as o.name+' ('+o.owner.firstName+' '+o.owner.lastName+')' group by o.selectGroup for o in players"></select>
                            <br>
                            <input type="button" id="match_start" value="Start" ng-click="start_match()"/>
                            <input type="button" id="match_stop" value="Stop" ng-click="abort_match();"/>
							<input type="checkbox" ng-model="auto_run"> Auto-Start after Save
						</div>
						<div ng-show="results.total_games&gt;0">
							<div class="results" style="display:block;">
								<h1 ng-show="results.player_wins[0]>0" style="display:block;float:none;">You WIN!</h1>
								<h1 ng-show="results.player_wins[1]>0" style="display:block;float:none;">You LOSE!</h1>
								<div class="message" ng-show="results.message">{{results.message}}</div>
							</div>
						</div>
                        <div id="game_canvas"></div>
                        <div ng-show="game.scenario">
                        	Game started with these options:
                        	<pre>{{game.scenario | json}}</pre>
	                    </div>
                    </div>
                </div>
                <div class="panel" id="panel_console">
                    <div class="panel_title">Console </div>
                    <div class="panel_controls">
                        <input type="button" value="clear" ng-click="log.length=0">
                        Show:
                            <input type="checkbox" ng-model="log_player">Player
                            <input type="checkbox" ng-model="log_game">Game
                            <input type="checkbox" ng-model="log_match">Match
                    </div>
                    <div class="panel_body">
                        <div id="console">
                            <div ng-repeat="message in log | log_filter:log_player:log_game:log_match" ng-class="message.type">{{message.message}}</div>
                        </div>
                    </div>
                </div>
                <div class="panel" id="panel_game_info">
                    <div class="panel_title">Game API/Help</div>
                    <div class="panel_body markdown" ng-bind-html="to_trusted(dev_game.rules)"></div>
                </div>
                <div class="panel" id="panel_help">
                    <div class="panel_title">Help</div>
                    <div class="panel_body">
HOW TO CREATE A BATTLESCRIPTS PLAYER

<div class="help_box">
	<div class="title">Core Concepts</div>
	<div class="body">
		A Player is a simple javascript object which can implement a few methods, and can call a few functions. It does not control the game in any way. Instead, it just responds to requests from the game controller. By coding logic into your player, you make it decide how to behave based on the input conditions it receives from the controller. The Player's code doesn't change while the game is running, so you must consider every possible situation in order for it to behave correctly.	</div>
</div>

<div class="help_box">
	<div class="title">Game State</div>
	<div class="body">
		Each method on the player accepts a JSON data structure, as defined by the game itself. You don't have any control over this structure. You can only inspect it and use it to make decisions about what to do. The data structure used by each game is different, and is documented by the game itself.	</div>
</div>

<div class="help_box">
	<div class="title">Losing A Game</div>
	<div class="body">
		How to win a game depends on the specific game you are playing. But your player can always lose a game by:
			<ul class="bullet">
				<li>Not implementing a move() method
				<li>Not making a move when asked
				<li>Making a move out of turn (calling move() twice, for example)
			</ul>
		Depending on the game, you may also lose a game in these ways:
			<ul class="bullet">
				<li>Making an invalid move
				<li>Taking too long to make a move
			</ul>
	</div>
</div>

<div class="help_box">
	<div class="title">Player Method API</div>
	<div class="body">
		Your Player must implement a move() method. Other methods are optional.

		<div class="api-method">
			<div class="title">Player.move(data)</div>
			<div class="body">
				The move() method is the core of your player. It gets passed a game-defined JSON structure that describes the current state of the game, and it must make a move by calling the global move() function. Games may have time limits on moves, so your move() method may need to be fast and efficient.			</div>
		</div>

		<div class="api-method">
			<div class="title">Player.start(scenario)</div>
			<div class="body">
				<p>
				Called when a game is ready to start, before the first move. It gets passed a JSON structure describing the conditions of the game, called the scenario. For example, it may define the total number of ships, how much energy you start with, etc. This structure is decided entirely by the game.				</p>
				<p>You may use the start() method to initialize your player with data that is maintained across moves.</p>
			</div>
		</div>

		<div class="api-method">
			<div class="title">Player.end( {"won":boolean,"winner":player_number} )</div>
			<div class="body">
				Called when a game ends			</div>
		</div>

		<div class="api-method">
			<div class="title">Player.match_end(???)</div>
			<div class="body">
				Called when a match (1 or more games) ends.			</div>
		</div>
	</div>
</div>

<div class="help_box">
	<div class="title">Player Global Function API</div>
	<div class="body">
		Your player may call the following functions.

		<div class="api-method">
			<div class="title">move( your_move, data )</div>
			<div class="body">
				Call the move() method to make your move. You must pass a first argument describing your move, which must match the syntax of whatever the game defines. Games may also require a second argument which can be a JSON structure with more data about your move.			</div>
		</div>

		<div class="api-method">
			<div class="title">log ( string )</div>
			<div class="body">
				Log a message to whatever the game controller is using to show log messages.			</div>
		</div>
	</div>
</div>
					</div>
                </div>
            </div>
        </div>
  </div>
</div>

</body>
</html>
