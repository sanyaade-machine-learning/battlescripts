var app = angular.module('battlescripts', ['restangular']);

app.directive('json', function() {
  return {
    restrict: 'A', // only activate on element attribute
    require: 'ngModel', // get a hold of NgModelController
    link: function(scope, element, attrs, ngModelCtrl) {
      function fromUser(text) {
        // Beware: trim() is not available in old browsers
        if (!text || text.trim() === '')
          return {};
        else
          // TODO catch SyntaxError, and set validation error..
          return angular.fromJson(text);
      }

      function toUser(object) {
          // better than JSON.stringify(), because it formats + filters $$hashKey etc.
          return angular.toJson(object, true);
      }

      // push() if faster than unshift(), and avail. in IE8 and earlier (unshift isn't)
      ngModelCtrl.$parsers.push(fromUser);
      ngModelCtrl.$formatters.push(toUser);

      // $watch(attrs.ngModel) wouldn't work if this directive created a new scope;
      // see http://stackoverflow.com/questions/14693052/watch-ngmodel-from-inside-directive-using-isolate-scope how to do it then
      scope.$watch(attrs.ngModel, function(newValue, oldValue) {
        if (newValue != oldValue) {
          ngModelCtrl.$setViewValue(toUser(newValue));
          // TODO avoid this causing the focus of the input to be lost..
          ngModelCtrl.$render();
        }
      }, true); // MUST use objectEquality (true) here, for some reason..
    }
  };
});

// A "reverse" filter for arrays
app.filter('reverse', function() {
  return function(items) {
    return items.slice().reverse();
  };
});

// Filtering of log messages
app.filter('log_filter',function() {
   return function(logs,log_player,log_game,log_match) {
       var show=[];
       angular.forEach(logs,function(msg) {
           if ( (log_player&&msg.type=="player_log") || (log_game&&msg.type=="game_log") || (log_match&&msg.type=="match_log")) {
               show.push(msg);
           }
       })
       return show;
   }
});

// CodeMirror
app.directive("codemirror", function($timeout) {
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

      var config = scope.$eval(attrs.codemirror) || {lineNumbers: true};
      for( var option in config )
        editor.setOption(option, config[option]);

      editor.setSize(null, elem.parentNode.clientHeight);

      //TODO Watch config scope.$watch(attr.codemirror, f(), true);
      //Fire digest on window resize
      window.addEventListener("resize", function() {
        $timeout(function() { //Prefered method to fire digest
          scope.$digest();
        });
      });
      //Listen for parent resize
      scope.$watch(
          function() { return elem.parentNode.clientHeight; },
          function() { editor.setSize(null, elem.parentNode.clientHeight); }
      );

      //Keeps the model in sync
      editor.on("change", function(editor, changeObj) { //TODO use changeObj to increase speed???
        var newValue = editor.getValue();
        editor.save();

        model.$setViewValue(newValue);
        if (!scope.$$phase) {
          scope.$apply();
        }
      });

      //Watches for model changes
      scope.$watch(attrs.ngModel, function(newValue, oldValue, scope) {
        if(newValue) {
          var position = editor.getCursor();
          var scroll = editor.getScrollInfo();
          editor.setValue(newValue);
          editor.setCursor(position);
          editor.scrollTo(scroll.left, scroll.top);
        }
      });
    }
  };
});

// Web Services
app.config(function(RestangularProvider) {
    RestangularProvider.setBaseUrl('/server/service');
	RestangularProvider.setDefaultHeaders({'Content-Type': 'application/json'});
});

app.factory('API', function(Restangular) {
/*
	Game Routes
X	$app->get('/games', 'GameActions::retrieveGames');
	$app->post('/games', 'GameActions::createGame');
X	$app->get('/games/{id}','GameActions::retrieveGame');
	$app->post('/games/{id}','GameActions::updateGame');
X	$app->get('/games/{id}/source','GameActions::retrieveGameSource');
X	$app->get('/games/{id}/players','PlayerActions::retrievePlayersForGame' );
	$app->get('/games/{id}/myplayers','PlayerActions::retrieveMyPlayers');

	Player Routes
X	$app->post('/players',"PlayerActions::createPlayer");
X	$app->get('/players/{playerId}','PlayerActions::retrievePlayer');
X	$app->get('/players/{id}/source','PlayerActions::retrievePlayerSource');
	$app->post('/players/{playerId}', 'PlayerActions::updatePlayer');
X	$app->delete('/players/{playerId}','PlayerActions::removePlayer');
X	$app->post('/players/{playerId}/publish', 'PlayerActions::publishPlayer');
*/
    // Custom methods to extend Restangular's Model and Collection objects for a nicer API
    // ===================================================================================
    // Things you can do to a game
    var game_methods = {
        getSource:function(success,error) {
            return this.one('source').get().then(success,error);
        },
        getPlayers:function(success,error) {
            return this.all('players').getList().then(success,error);
        },
        getMyPlayers: function(success,error) {
            return this.all('myplayers').getList().then(success,error);
        },
        getDefaultPlayer: function(success,error) {
            return this.one('templateSource').get().then(success,error);
        }
    };
    // Things you can do to a player
    var player_methods = {
        getSource: function(success,error) {
            return this.one('source').get().then(success,error);
        },
        publish: function(success,error) {
            return this.customPOST( {}, "publish", success, error);
        },
        unpublish: function(success,error) {
            return this.customPOST( {}, "unpublish", success, error);
        },
        delete: function(success,error) {
            return this.delete(success, error);
        },
        save: function(success,error) {
            return this.post().then(success,error);
        }

    };

    // Define the API
    // ==============
	var api = {};

    // GAMES
    api.getGames = function(success,error) { return Restangular.all('games').getList().then(success,error); }
    api.getGame = function(id,success,error) { return Restangular.one('games',id).get().then(success,error); }
    // Extend the "Game" model
    Restangular.extendModel('games', function(game) {
        return angular.extend(game,game_methods);
    });

    // PLAYERS
    api.getPlayer = function(id,success,error) { return Restangular.one('players',id).get().then(success,error); }
    api.createPlayer = function(data,success,error) {
        return Restangular.all('players').post(data).then(success,error);
    }
    // Extend the "Player" model
    Restangular.extendModel('myplayers', function(player) {
        return angular.extend(player,player_methods);
    });
    Restangular.extendModel('players', function(player) {
        return angular.extend(player,player_methods);
    });

    return api;
});