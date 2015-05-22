_.mixin({
  indexOfBy: function(array, f, fromIndex) {
    fromIndex = fromIndex || 0;
    f = f || _.identity;
    
    if(fromIndex > array.length) return -1;
    
    for(var i = fromIndex;i < array.length; i++)
      if(f(array[i]))
        return i;
  }
});

angular.module('BattleScripts', ['ngResource']).config([ "$locationProvider", function($locationProvider) {
  $locationProvider.html5Mode(true);
}]).directive("codemirror", function() {
  return {
    restrict: 'A',
    priority: 0, 
    require: '^ngModel',
    link: function (scope, elems, attrs, model) {
      var elem = elems[0];
      
      if( window.CodeMirror === undefined ) throw new Error("The CodeMirror library is not available");
      if( !attrs.ngModel ) throw new Error("ng-model is required for linking code-mirror");
      if( elem.type !== "textarea" ) throw new Error("Only textarea is supported right now...");
      
      var editor = CodeMirror.fromTextArea(elem);
        
      //TODO Watch config scope.$watch(attr.codemirror, f(), true);
      var config = scope.$eval(attrs.codemirror) || {lineNumbers: true};
      for( var option in config )
        editor.setOption(option, config[option]);
        
      editor.on("change", function(editor, changeObj) { //TODO use changeObj to increase speed???
        var newValue = editor.getValue();
        editor.save();
          
        model.$setViewValue(newValue); 
        if (!scope.$$phase) {
          scope.$apply();
        }
      });
              
      scope.$watch(
        function() { return elem.value; },
        function(newValue, oldValue, scope) {
          var position = editor.getCursor();
          var scroll = editor.getScrollInfo();
          editor.setValue(newValue);
          editor.setCursor(position);
          editor.scrollTo(scroll.left, scroll.top);
        }
      );
    }
  };
}).factory("Games", [ "$http", function($http) {
  var games = ["elevator-action", "tron", "tic-tac-toe"];
  
  return {
    get: function(i) { 
      if(i === undefined)
        return games;
      return {"id":2,"name":"Tron","synopsis":"Based off Disney's 1982 film where players had to cut off their opponents using the light cycles that their motor bikes left behind.","description":"A much longer description of the rules and stuff.","version":"v0.0.9","min_players":2,"max_players":4};
    },
    source: function(i) {
      return $http.get("/games/"+games[i]+"/game.js");
    },
    template: function(i) {
      return $http.get("/games/"+games[i]+"/canvas.html");
    }
  };
}]).factory("Players", [ "$http", function($http) {
  var players = ["dumb.js", "random.js", "taylor.js", "matt.js"];
  
  return {
    get: function(game, i) { 
      if(i === undefined)
        return players;
      return {"id":0,"name":"Dumb"};
    },
    source: function(game, i) {
      return $http.get("/games/" + game + "/players/" + players[i]);
    }
  }
}]).controller('BSIDE', ['$scope', '$location', 'Games', 'Players', "$compile", "$http", function($scope, $location, Games, Players, $compile, $http) {
  //Configuration Variables
  $scope.conf = {
    mode: "javascript",
    theme: "solarized light",
    indentUnit: 2,
    smartIndent: true,
    tabSize: 2,
    indentWithTabs: false,
    electricChars: true,
    lineWrapping: false,
    lineNumbers: true,
    undoDepth: 1000,
    historyEventDelay: 200
  };
  
  //State variables
  $scope.menu = '';
  $scope.mode = "source";
  $scope.games = Games.get();
  $scope.game = $location.search().game;
  $scope.players = undefined;
  $scope.player = $location.search().player;
  $scope.playerData = undefined;
  
  $scope.panels = [{
    name: "Game",
    type: "code",
    config: $scope.config,
    meta: {"id":2,"name":"Tron","synopsis":"Based off Disney's 1982 film where players had to cut off their opponents using the light cycles that their motor bikes left behind.","description":"A much longer description of the rules and stuff.","version":"v0.0.9","min_players":2,"max_players":4},
    model: null,
    visible: true
  },{
    name: "Canvas",
    type: "code",
    config: $scope.config,
    model: null,
    visible: true
  },{
    name: "Player",
    type: "code",
    config: $scope.config,
    model: null,
    visible: true
  },{
    name: "Output",
    type: "html",
    config: $scope.config,
    model: undefined,
    visible: true
  }];
  
  //Watching state changes
  $scope.$watch(
    function() { return $location.search().game; },
    function(game) {
      $scope.game = game;
      $scope.gameData = Games.get(i);
      //TODO Number of players
      Games.source(game).then(function(data) { $scope.panels[0].model = data.data; });
      Games.template(game).then(function(data) { $scope.panels[1].model = data.data; });
      $scope.players = Players.get();
    }
  );
  $scope.$watch(
    function() { return $location.search().player; },
    function(player) {
      $scope.player = player;
      $scope.playerData = Players.get($scope.games[$scope.game], player);
      Players.source($scope.games[$scope.game], player).then(function(data) { $scope.panels[2].model = data.data; });
    }
  );
  
  $scope.$watch(
    function() { return $scope.panelsVisible(); },
    function() { $scope.initSizes(); }
  );
  
  //Functions used in the view
  $scope.route = function(param, value) { $location.search(param, value); $scope.menu = '';};
  $scope.panelsVisible = function() { return _.reduce($scope.panels, function(acc, n) { return acc + (n.visible === true); }, 0)};
  $scope.lastVisible = function() { return _.findLastIndex($scope.panels, "visible"); };
  $scope.nextVisible = function(i) { return _.indexOfBy($scope.panels, function(elem) { return elem.visible }, i+1); };
  $scope.initSizes = function() {
    var panels = document.querySelectorAll(".panel");
    _.each(panels, function(editor) {
        if(!editor.style) editor.style = "";
        editor.style.width = Math.floor(document.body.offsetWidth / $scope.panelsVisible()) + "px";
        editor.style.height = document.getElementsByTagName("main")[0].offsetHeight + "px";
    });
  };
  $scope.resize = function(num, event) {
    var elemL = document.getElementById("panel"+num);
    var elemR = document.getElementById("panel"+$scope.nextVisible(num));
    var widthL = elemL.offsetWidth;
    var widthR = elemR.offsetWidth;
  
    var start = [event.x, event.y];
    document.body.style.cursor = "col-resize";
    document.onselectstart = function() {return false;};
    
    document.onmousemove = function(event) {
      if(event.x > elemL.offsetLeft && event.x < elemR.offsetLeft + elemR.offsetWidth - 4) {
        elemL.style.width = widthL + event.x - start[0] + "px";
        elemR.style.width = widthR - event.x + start[0] + "px";
      }
    };
    
    document.onmouseup = function() {
      document.onselectstart = null;
      document.body.style.cursor = "";
      document.onmousemove = null;
    };
  };
  $scope.action = function(x) {
    if(x === "New Game")
      $scope.newGame();
  };
  $scope.newGame = function() { console.log("Create Game"); };
  
  
  //TODO ??
  $scope.libs = {};
  $scope.loadLibraries = function(libs) {
    var whitelist = {
      "game-api": "./games/game-api.js",
      "player-api": "./games/player-api.js",
      "underscore": "./js/underscore.js",
      "lodash": "./js/lodash.js"
    };
    libs = libs || Object.keys(whitelist);
                
    _.each(libs,function(lib) {
      var libPath = whitelist[lib];
      if(libPath && !$scope.libs[lib])
        $http.get(libPath).then(function(response) {
          $scope.libs[lib] = response.data;
        });
    });
  };
  $scope.runGame = function() {
    var createWorker = function(src) {
      var importScripts = src.match(/importScripts\(.+\)/g);
      var scripts = _(importScripts).map(function(str) { return str.match(/("|').+?("|')/g); }).flatten().value();
      var scripts2 = _.map(scripts, function(x) { return x.replace(/(\.js|"|'|(.+\/))/g,""); });
                    
      var srcLibed = _.reduce(scripts2, function(acc, script) { return acc + $scope.libs[script] + "\n\n"}, "") + src.replace(/importScripts\(.+\)/g,"");
      return new Worker(URL.createObjectURL(new Blob([srcLibed])));
    };
  
    document.getElementById("panel3").innerHTML = $scope.panels[1].model;
    $compile(panel3)($scope);
                    
    var game = createWorker($scope.panels[0].model);
    var players = [];
    players.push(createWorker($scope.panels[2].model));
    players.push(createWorker($scope.panels[2].model));
    
    //WTF not done compiling???
    setTimeout(function() {
      GameController.start_match({
        game: game,
        players: players,
        canvas: "#panel3",
        total_games: 1,
        move_delay: 0,
        scenario: {}
      });
    }, 100);

    
  };

  angular.element(document).ready($scope.initSizes);
  $scope.loadLibraries();
}]);
