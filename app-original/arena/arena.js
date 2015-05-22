app.controller("ArenaCtrl", ["$scope", "$timeout", "Restangular", "$compile", function($scope, $timeout, Restangular, $compile) {
  //Battle Related
  this.game       = undefined;
  this.gladiators = [];
  this.matchPlayers = [];
  this.arbiter    = Arbiter.create();
  this.match      = undefined;
  this.matchOpts  = {
    total_games : 6,
    move_delay  : 100,
    time_limit  : 0
  };
  this.results    = {total_games: 0, games_played: 1, current_game: 0, player_wins: [0,0], draws: 0};

  //UI Related
  this.games   = [];
  this.games   = Restangular.all('games').getList().$object;
  this.players = [];

  //Compiles the game template and refetches players;
  this.prepareCanvas = function() {
    var canvas = document.querySelector("#canvas");
    canvas.innerHTML = this.game.canvas;
    $compile(canvas)($scope);

    $timeout(function() { $scope.$apply(); }); //Prefered safe way to start a digest
    this.fetchPlayers();
  };

  this.fetchPlayers = function() {
    this.game.all('players').getList().then(function(players) {
      this.players = players;
    }.bind(this), function(e) { //TODO is having to use .bind(this) a bug in restangular???
      alert("Error fetching players...");
    });
  };

  this.startMatch = function() {
    if(!this.match || !this.match.started) {
      var arbiter = this.arbiter;
      var canvas = document.querySelector("#canvas");

      var gameSRC   = "/server/service/games/" + this.game.id + "/source";
      var game = new Game(gameSRC, arbiter);

      this.matchPlayers  = _.map(this.gladiators, function(p) {
        var psrc = "/server/service/players/" + p.id + "/source";
        var pl = new Player(psrc, arbiter);
        pl.name = p.name;
        return pl;
      });
      var players = this.matchPlayers;
      
      var id1 = arbiter.subscribe("*", function(msg, key) { console.log(key + ": " + JSON.stringify(msg)); });
      var id2 = arbiter.subscribe("Game/Render", {priority: 10} ,function(data) {
        var $scope = angular.element(canvas).scope();
        if ($scope) 
        $timeout(function() { $scope.$apply(function() { $scope.game = data; }); });
        else
          console.log("$scope not found");
      });

      this.match = new Match(game, players, this.matchOpts, arbiter);
      var id25 = arbiter.subscribe("Match/Results", function(results) {
        this.results = results;
        $timeout(function() { $scope.$apply(); });
      }.bind(this));
      var id3 = arbiter.subscribe("Match/End", function() {
        arbiter.unsubscribe(id1);
        arbiter.unsubscribe(id2);
        arbiter.unsubscribe(id25);
        arbiter.unsubscribe(id3);
        game.worker.terminate();
        _.each(players, function(p) { p.worker.terminate(); });

      });

      this.match.start({});
    }
  };
}]);


