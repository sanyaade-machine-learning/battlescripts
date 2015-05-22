// Layout Functionality
// ====================
$(function() {
    $('.panel_button').each(function(i,button) {
        var $button = $(button);
        var rel = $button.attr('rel');
        $('#'+rel).toggleClass('hide',!$button.hasClass('active'));
        $button.click(function() {
            $button.toggleClass('active');
            $('#'+rel).toggleClass('hide',!$button.hasClass('active'));
        });
    });
    $('.panel_title').click(function(e) {
        var panel = e.target.parentNode;
        //If the parent isn't a panel, abort!
        if (!$(panel).is('.panel')) { return; }
        // Find other panels that are not hidden
        var $panels = $('.panel:not(.hide):not(.minimized)').not(panel);
        if ($panels.length>0) {
            $panels.addClass('minimized');
            $(panel).addClass('maximized');
        }
        else {
            // Restore the minimized panels
            $('.panel.minimized').removeClass('minimized');
            $(panel).removeClass('maximized');
        }
    }).attr('title','Click to maximize/restore');
});

// A custom "Player" object that is sync, for dev purposes
// =======================================================
var DevPlayer = function(src) {
  this.player_number = undefined;
  this.player = null;
  this.loaded = false;
  if (src) {
	this.loaded = true;
    this.compile(src);
  }
  var self = this;
};
DevPlayer.prototype.compile = function(src) {
    if (src) { this.src = src; }
    var api = "var player_number = undefined; \n";
    api += "var move = function(move,data) { Arbiter.publish('Player/Move', { 'player_number':player_number, 'move':move}); return move; }; \n";
    api += "var log = function(msg) { Arbiter.publish('Log/Player', {'message':msg, 'player_number':player_number} ); }; \n";
    api += "var p = new Player(); \n";
    api += "p.set_player_number = function(num) { player_number=num; } \n";
    api += "return p; \n";
    try {
        this.player = eval("(function() { "+this.src+api+" }())");
    } catch (e) {
        return e;
    }
    if (typeof this.player.move!="function") {
        return new Error("Player doesn't have a move() function!");
    }
    this.player.player_number = this.player_number;
    return null;
}
DevPlayer.prototype.init = function(data) { }
// On game start, pass the player number from the DevPlayer to the compiled Player object
DevPlayer.prototype.start = function(data) {
    this.player.set_player_number(data.player_number);
    if (typeof this.player.start=="function") {
	    this.player.start(data);
	}
    Arbiter.publish('Player/Ready',data.player_number);
}
DevPlayer.prototype.move = function(data) {
	try {
	    return this.player.move(data);
	} catch(e) {
		Arbiter.publish('Error/Runtime',e);
	}
}
DevPlayer.prototype.end = function(data) {
    if (typeof this.player.end=="function") {
	    this.player.end(data);
	}
}
DevPlayer.prototype.match_end = function(data) {
    if (typeof this.player.match_end=="function") {
	    this.player.match_end(data);
	}
}
DevPlayer.prototype.error = function(data) { }

// Angular-related scope Functions
// ===============================
function PlayerDeveloperController($scope,$compile,$http,Restangular) {
	$http.defaults.withCredentials = true; // Necessary for CORS

   $scope.conf = {
    mode: "javascript",
    theme: "mdn-like",
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

    $scope.games = [];
	// On controller load, make a request to get the games list
	$scope.games = Restangular.all('games').getList().$object;

    $scope.players = null;
    $scope.myplayers = undefined;
    $scope.dev_game = null;
    $scope.player = null;
    $scope.opponent = null;
	$scope.auto_run = false;
	$scope.left_nav_open = true;
    $scope.compile_status = "success";
    $scope.compile_message = "";
    $scope.testing_move = false;
    $scope.log_match = false;
    $scope.log_game = false;
    $scope.log_player = true;
    $scope.log = [];
	$scope.move_test_input = "{}";
	$scope.move_test_use_runner_data = true;

	$scope.test = function(g) { alert(JSON.stringify(arguments)); }

    $scope.$watch('dev_game.id',function(value,old) {
        if (value!=old) {
			Restangular.one('games',value).get().then(function(game) {
				$scope.dev_game = game;

                var canvas = document.getElementById('game_canvas');
                canvas.innerHTML = $scope.dev_game.canvas;
                $compile(canvas)($scope);

				// Retrieve the list of my players
				$scope.get_myplayers();

				// Retrieve a list of ALL players for this game
				$scope.get_players();
			});
        }
    });
    $scope.get_myplayers = function() {
		// Retrieve a list of my players for this game
		$scope.dev_game.all('myplayers').getList().then(function(players) {
			$scope.myplayers = players;
		},function(e) {
			if (e && e.status && e.status==401) {
				$scope.myplayers=null;
			}
		});

    };
    $scope.get_players = function() {
		$scope.dev_game.all('players').getList().then(function(players) {
			$scope.players = players;
			angular.forEach(players,function(p,i) {
				if ("Y"===p.defaultOpponentFlag) {
					$scope.opponent = p;
					return;
				}
			});
		});
    };
    $scope.login_my_players = function() {
		$.when(login()).then(function(result){
			$scope.get_myplayers();
		});
    };
    $scope.$watch("player.id",function(value,old) {
        if (value!=old) {
            // Retrieve the new player's source
            player.loaded = true;
            player.compile($scope.player.source);
       }
    });
    $scope.$watch("player.source",function(value,old) {
        if (value!=old) {
            var error = player.compile(value);
            if (error) {
                $scope.compile_status="error";
                var line = error.lineNumber || error.line || "(unknown)";
                $scope.compile_message = "Line #"+line+": "+error.message;
            }
            else {
                $scope.compile_status="success";
                $scope.compile_message = "OK";
            }
        }
    });
    $scope.publish = function() {
		if ($scope.player.id) {
			// UPDATE
			$scope.player.customPOST( {} , "publish" ).then(
				function(published_on) {
					// Refresh the opponent list, in case this one needs to be added in
					$scope.get_players();
					alert( JSON.stringify(published_on) );
				},
				function(e) {
					// HANDLE ERROR
					alert("Error publishing player");
				}
			);
		}
    }
	$scope.save = function() {
		if ($scope.player.id) {
			// UPDATE
			$scope.player.post().then(
				function(data) {
					Arbiter.publish('player/save/success','Player saved');
				},
				function(e) {
					if (e.status==401) {
						// Not authenticated
						Arbiter.publish('player/create/error',"You must be logged in to save a player!");
						$.when(login()).then(function(result){
							$scope.save();
						}, function(err){
							//alert(err);
						});
					}
					else {
						Arbiter.publish('player/save/error',"Error saving player ["+e.status+"]:"+e.data);
					}
				}
			);
		}
		else {
			// CREATE
			$scope.player.gameId = $scope.dev_game.id;
			Restangular.all('players').post($scope.player).then(
				function(data) {
					Arbiter.publish('player/create/success','Player created');
					$scope.player = data;
					$scope.myplayers.push($scope.player);
				},
				function(e) {
					if (e.status==401) {
						// Not authenticated
						Arbiter.publish('player/create/error',"You must be logged in to create a player!");
						$.when(login()).then(function(result){
							$scope.save();
						}, function(err){
							//alert(err);
						});
					}
					else {
						Arbiter.publish('player/create/error',"Error creating player ["+e.status+"]:"+e.data);
					}
				}
			);
		}
	};
	$(document).bind('keydown', function(e) {
	  if(e.ctrlKey && (e.which == 83)) {
		e.preventDefault();
		if ($scope.player && $scope.player.id) {
			$scope.save();
		}
		return false;
	  }
	});
	$scope.create = function() {
		$scope.player = {};
		$scope.player.gameId = $scope.dev_game.id;
		$scope.player.source = $scope.dev_game.template_source || "function Player() {\n\n\tthis.move = function(game) {\n\t\tlog(\"Moving up!\");\n\t\treturn move(\"UP\");\n\t};\n\n\tthis.start = function(scenario) { };\n\n}";
	};

    $scope.test_move = function() {
        $scope.testing_move = true;
        try {
            var o = eval("("+$scope.move_test_input+")");
            player.move(o);
        }
        catch(e) {
            $scope.move_test_output = "JSON Error:"+e;
            $scope.testing_move = false;
        }
    };

	$scope.display_message = function(msg,error) {
		$scope.message = msg;
		$scope.message_error = error || false;
		$('#message').slideDown();
		setTimeout(function() {
			$('#message').slideUp(function() {
				$scope.message=null;
			});
		},3000);
	};

	$scope.toggle_left_nav = function($event) {
		$scope.left_nav_open = !$scope.left_nav_open;
	};

    // Arbiter Message Handling
    // ========================
    // Subscribe to the Render event to draw the game
    Arbiter.subscribe('Game/Render', function(obj) {
        $scope.$apply(function() {
            $scope.game = obj;
        });
    });
    // Subscribe to the Results event to draw results
    Arbiter.subscribe('Match/Results', function(obj) {
        $scope.$apply(function() {
            $scope.results = obj;
        });
    });
    // Create an Arbiter listener at a high priority to listen to the game move first, in case
    // we are in "test move" mode, so we can capture it
    Arbiter.subscribe( 'Player/Move', {priority:500}, function(data){
        if ($scope.testing_move) {
            $scope.move_test_output = JSON.stringify(data.move);
            $scope.testing_move = false;
            return false; // Cancel the message to other subscribers
        }
    } );
	Arbiter.subscribe('player/save/*',function(msg) {
		$scope.display_message(msg);
	});
	Arbiter.subscribe('player/create/*',function(msg) {
		$scope.display_message(msg);
	});

	Arbiter.subscribe('Game/Get_Move',function(obj) {
		if ($scope.move_test_use_runner_data) {
			$scope.move_test_input = JSON.stringify(obj.data,null," ");
		}
	});

	Arbiter.subscribe('Error/Runtime',function(e) {
		// Check the exception stack to see if we can extract the line number from it
		$scope.compile_status="error";
		var line = e.lineNumber || e.line;
		if (!line) {
			try {
			// Try to derive the line number from the stack
				var res = e.stack.match(/<anonymous>:(\d+):\d+/);
				if (res && res.length && res[1]) {
					line = res[1];
				}
			} catch(e) { }
		}
		if (!line) {
			line = "(unknown)";
		}
		$scope.compile_message = "Line #"+line+": "+e.message;
	});

    // Logging
    // =======
    var console = document.getElementById('console');
    function log(type,s,cn) {
	    if (!type || !s) { return; }
        var msg = JSON.stringify( (typeof s.message!="undefined")?s.message:s )
        $scope.log.push( {"type":type,"message":msg} );
        console.parentNode.scrollTop = 9999999;
    }
    Arbiter.subscribe( 'Log/Match', function(s) { log("match_log",s); } );
    Arbiter.subscribe( 'Log/Game',  function(s) { log("game_log",s); } );
    Arbiter.subscribe( 'Log/Player', function(s) { if (s.player_number==1) { delete s.player_number; log("player_log",s); } } );

	// Match Controls
	// ==============
	var player = new DevPlayer();
	var match = undefined;

	$scope.start_match = function() {
		// START THE MATCH!!!
		var game = new Game($scope.dev_game.id);
		var players = [ new DevPlayer(player.src), new DevPlayer($scope.opponent.source) ];
		if (match) {
			match.abort();
		}
		match = new Match(game,players,{total_games:1});
		setTimeout(function() {
			match.start( {} );
		},0);
	}
	$scope.abort_match = function() {
	  match.abort();
	}
	// Listen for save, and conditionally auto-start a match
	Arbiter.subscribe('player/save/success',function() {
		if ($scope.auto_run) {
			match.abort();
			$scope.start_match();
		}
		$scope.test_move();
	});

}

