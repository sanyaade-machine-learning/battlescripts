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
  <title>Arena</title>
  <link rel="stylesheet" type="text/css" href="../css/reset.css"/>
  <link rel="stylesheet" type="text/css" href="../css/devplayer.css"/>
  <link rel="stylesheet" type="text/css" href="./arena.css"/>
  <link href='//fonts.googleapis.com/css?family=Nixie+One|Ruda:400,900,700' rel='stylesheet' type='text/css'>
</head>
<body id="wrap" class="wrap" ng-app="battlescripts" ng-controller="ArenaCtrl as Arena" ng-class="{ready: true}">
  <header>
    <h1>BattleScripts Arena</h1>
    <ul>
       <li>[<a href="/">home</a>] </li>
       <li>[<a href="/devplayer">player developer</a>] </li>
       <li>[<a href="/contact.html">contact</a>] </li>
    </ul>
  </header>
  <main>
    <div id="left-panel" ng-if="Arena.game">
      <div class="container">
        <label for="game-select">Game</label>
        <select ng-if="Arena.games.length" ng-model="Arena.game" ng-options="g as g.name for g in Arena.games" ng-change="Arena.prepareCanvas()"></select>
      </div>
      <div class="container">
        <div ng-if="!Arena.players.length">Loading Players...</div>
        <div ng-repeat="(i, p) in Arena.gladiators track by $index">
          <h6>
            Player {{i+1}}:
            <span ng-click="Arena.gladiators.splice(i, 1)" class="delete">X</span>
          </h6>
          <h4 style="padding-left: 10px;">{{p.name}}</h4>
        </div>
        <div ng-if="Arena.players.length && Arena.gladiators.length < Arena.game.maxPlayers">
          <h6>Player {{Arena.gladiators.length + 1}}
          <select ng-model="player" ng-options="p as p.name+' ('+p.owner.firstName+' '+p.owner.lastName+')' for p in Arena.players" ng-change="Arena.gladiators.push(player)"></select>
        </div>
      </div>
      <div class="container">
        <h2>Match Options</h2>
        <div ng-repeat="(mok, mov) in Arena.matchOpts">
          <label>{{mok}}</label>
          <input type="text" ng-model="Arena.matchOpts[mok]" />
        </div>
      </div>
      <div class="container">
        <button ng-if="Arena.gladiators.length >= Arena.game.minPlayers && Arena.gladiators.length <= Arena.game.maxPlayers" ng-click="Arena.startMatch()">Start</button>
        <button ng-if="Arena.match && Arena.match.started" ng-click="Arena.match.abort()">Stop</button>
      </div>
    </div>
    <section class="dialog" ng-if="!Arena.game">
      <p>First, select a game</p>
      <label for="game-select">Game</label>
      <div ng-if="!Arena.games.length">Loading Games...</div>
      <select ng-if="Arena.games.length" ng-model="Arena.game" ng-options="g as g.name for g in Arena.games" ng-change="Arena.prepareCanvas()"></select>
    </section>
    <section ng-show="Arena.game">
      <h2 class="center-text">{{Arena.game.name}}</h2>
      <h4 id="canvas"></h4>
      <div ng-repeat="(i, p) in Arena.matchPlayers" style="width: 250px; display: inline-block;">{{p.name}}: {{Arena.results.player_wins[i]}}</div>
    </section>
  </main>
  <script type="text/javascript" src="../js/lodash.js"></script>
  <script type="text/javascript" src="../js/angular.js"></script>
  <script type="text/javascript" src="../js/angular-resource.js"></script>
  <script type="text/javascript" src="../js/restangular.js"></script>
  <script type="text/javascript" src="../js/battlescripts-app.js"></script>
  <script type="text/javascript" src="./arena.js"></script>
  <script type="text/javascript" src="../js/Arbiter.js"></script>
  <script type="text/javascript" src="../js/Match.js"></script>
  <script type="text/javascript" src="../js/modalLogin.js"></script>
  <script type="text/javascript" src="../js/hello.min.js"></script>
</body>
</html>
