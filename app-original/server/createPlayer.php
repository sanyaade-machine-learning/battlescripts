<?php
require_once 'UtilityFunctions.php';
//$_SESSION["user"]["id"]=1;
if(!isset($_SESSION['user'])||$_SESSION['user']['id']<1){
	// redirect him back to login page
	header( "Location: /index.php?error=Your are not connected or your session has expired" );
}
	
/** JSON looks like this
 * 
 * {
	"id":"1",
	"name":"New game name",
	"description":"game description ere",
	"private":false,
	"synopsis":"Synopsis",
	"source":"source",
	"rules":"rules",
	"canvas":"canvas here",
	"minPlayers":"1",
	"maxPlayers":"5",
	"version":null,
	"createdBy":	
		{"id":null,
		"firstName":null,
		"lastName":null,
		"authProvider":null,
		"authId":null,
		"email":null,
		"superUser":false,
		"games":[],
		"players":[]},
	"options":[
		{"id":"1",
		"gameId":"1",
		"label":"Number tracks",
		"type":"NUMBER",
		"defaultValue":"3"}
		],
	"scenarios":[
		{
		"id":"1",
		"gameId":"1",
		"description":"1",
		"name":"1",
		"data":"1"}
		]
}
 * 
 * */

?>
<html>
<head>
<script src="jquery-2.0.3.js"></script>
<script>
function createPlayer() {
$.post("service/games/1/players",
		{
			"name":"Service Player for Game 1 Test 1",
			"description":"Random player",
			"source":"javascript source in here",
			"version":"1.0",
			"gameId":"1"

		},
		function(data) {
			$("#link").html("Created here: service/games/1/players/"+data.id);
		},
		"JSON"
		);

}
function createGame() {
	$.post("service/games",
			{
		"name":"New game name",
		"description":"game description ere",
		"synopsis":"Synopsis",
		"source":"source",
		"rules":"rules",
		"canvas":"canvas here",
		"minPlayers":"1",
		"maxPlayers":"5",
		"version":"1.0",
		"options":[
			{"label":"Number tracks",
			"type":"NUMBER",
			"defaultValue":"3"}
			],
		"scenarios":[
			{
			"description":"1",
			"name":"1",
			"data":"1"}
			]
	},
			function(data) {
				$("#link").html("Created here: service/games/"+data.id);
			},
			"JSON"
			);

	}
</script>
</head>
<body>
<input type="button" onclick="createPlayer()" value="Create Player"/>
<input type="button" onclick="createGame()" value="Create Game"/>
<br/>
<div id="link">

</div>


</body>

</html>