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
DevPlayer.prototype.error = function(data) { };

// Angular-related scope Functions
// ===============================
function PlayerDeveloperController($scope,$sce, $compile,$http,API) {
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
   $scope.to_trusted = function(html_code) {
	    return $sce.trustAsHtml(html_code);
	}

    $scope.games = [];
	// On controller load, make a request to get the games list
	API.getGames(function(games) {
        $scope.games = games;
    });

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
    $scope.log_game = true;
    $scope.log_player = false;
    $scope.log = [];
	$scope.move_test_input = "{}";
	$scope.move_test_use_runner_data = true;
	$scope.show_advanced_options = false;
    $scope.player_dev = true; // Let the game template show special dev-related messages

	$scope.test = function(g) { alert(JSON.stringify(arguments)); };

    var createShadowRoot = document.body.createShadowRoot ? "createShadowRoot" : "webkitCreateShadowRoot";
    var shadowCanvas = document.getElementById("game_canvas")[createShadowRoot]();

    $scope.$watch('dev_game.id',function(value,old) {
        if (value!=old) {
			API.getGame(value,function(game) {
				$scope.dev_game = game;

                var angularStyles = "<style type='text/css'>[ng\\:cloak],[ng-cloak],[data-ng-cloak],[x-ng-cloak],.ng-cloak,.x-ng-cloak,.ng-hide{display:none !important;}ng\\:form{display:block;}.ng-animate-start{border-spacing:1px 1px;-ms-zoom:1.0001;}.ng-animate-active{border-spacing:0px 0px;-ms-zoom:1;}</style>";
                shadowCanvas.innerHTML = angularStyles + $scope.dev_game.canvas;
                $compile(shadowCanvas)($scope);

				// Retrieve the list of my players
				$scope.get_myplayers();

				// Retrieve a list of ALL players for this game
				$scope.get_players();
			});
        }
    });
    $scope.get_myplayers = function() {
		// Retrieve a list of my players for this game
		$scope.dev_game.getMyPlayers(
            function(players) {
                $scope.myplayers = players;

                players[0].getSource(function(src) {
                    alert('1');
                    alert(src);
                })
		}, function(e) {
            },function(e) {
                if (e && e.status && e.status==401) {
                    $scope.myplayers=null;
                }
            }
        );
    };
    $scope.get_players = function() {
		$scope.dev_game.getPlayers(
            function(players) {
                $scope.players = players;
                angular.forEach(players,function(p,i) {
                    if ("Y"===p.defaultOpponentFlag) {
                        $scope.opponent = p;
                        return;
                    }
                    if ("Y"===p.testOpponentFlag) {
                        p.selectGroup="Developer Test Opponents";
                    }
                    else {
                        p.selectGroup="Other Players";
                    }
                });
    		}
        );
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
			$scope.player.publish(
				function(published_on) {
					Arbiter.publish('player/publish/success','Player published');
					// Refresh the opponent list, in case this one needs to be added in
					$scope.get_players();
				},
				function(e) {
					// HANDLE ERROR
					Arbiter.publish('player/publish/error','Error in publishing');
				}
			);
		}
    }
    $scope.delete = function() {
    	if(confirm('Are you sure you want to delete the player?')){
			if ($scope.player.id) {
				// DELETE
				$scope.player.remove().then(
					function(data) {
						//alert( JSON.stringify(data) );
						//load create because the current player they have been working on has been deleted
						$scope.create();
					},
					function(e) {
						// HANDLE ERROR
						alert("Error deleting player");
					}
				);
			}
			else{
				alert('Please select a player to delete and then press delete');
			}
    	}
    }
    $scope.unpublish = function() {
    	alert("Sorry, this feature is not yet available");
    }
    $scope.revert = function() {
    	alert("Sorry, this feature is not yet available");
    }
	$scope.save = function() {
		if ($scope.player.id) {
			// UPDATE
			$scope.player.save(
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
			API.createPlayer($scope.player,
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
		$scope.player.source = "Loading Player template...";
		// Get the default player code for this game
		API.getGameDefaultPlayer($scope.dev_game.id).then(function(source) {
			$scope.player.source = source;
		},function() {
			$scope.player.source = "function Player() {\n\n\tthis.move = function(game) {\n\t\treturn move(\"GO\");\n\t};\n\n\tthis.start = function(scenario) { };\n\n}";
		});
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
		}, error?10000:4000);
	};

	$scope.toggle_left_nav = function($event) {
		$scope.left_nav_open = !$scope.left_nav_open;
	};

    // Arbiter Message Handling
    // ========================
    // When the game starts, put config data into the scope
    Arbiter.subscribe('Game/Start',function(scenario) {
        $scope.$apply(function() {
            $scope.scenario = scenario;
        });
    });
    // Subscribe to the Render event to draw the game
    Arbiter.subscribe('Game/Render', function(obj) {
            $scope.game = obj;
        $scope.$apply();
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
	Arbiter.subscribe(['player/save/success','player/create/success','player/publish/success'],function(msg) {
		$scope.display_message(msg);
	});
	Arbiter.subscribe(['player/save/error','player/create/error','player/publish/error'],function(msg) {
		$scope.display_message(msg,true);
	});

	Arbiter.subscribe('Game/Get_Move',function(obj) {
		if ($scope.move_test_use_runner_data && obj.player_number==1) {
			$scope.move_test_input = JSON.stringify(obj.data,null," ");
		}
	});

	Arbiter.subscribe('Error/Runtime',function(e) {
        $scope.$apply(function() {
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
                } catch(e2) { window.console.log("FDSAFDSA"); }
            }
            if (!line) {
                line = "(unknown)";
            }
            $scope.compile_message = "Line #"+line+": "+e.message;
            $scope.results.message = $scope.compile_message;
        });
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
    // if the player compiles
    if($scope.compile_status === "success") {
		var game = new Game($scope.dev_game.id);
		var players = [ new DevPlayer(player.src), new DevPlayer($scope.opponent.source) ];
		if (match) {
			match.abort();
		}
		match = new Match(game,players,{total_games:1});
		setTimeout(function() {
			match.start( {} );
		},0);
    } else {
      $scope.display_message("Cannot start match while player does not compile", true);
	}
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

