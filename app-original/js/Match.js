"use strict";

_.mixin({
  repeat: function(e, n) {
    var result = [];
    for(var i=0; i< n; i++)
      result.push(e);
    return result;
  },
  rotateM: function(arr) {
    return arr.unshift(arr.pop());
  }
});

// ========================================================================
// Player Object
// ========================================================================
var Player = function(id, arbiter) {
  this.worker = undefined;
  this.arbiter = arbiter || Arbiter;
  this.player_number = undefined;
  this.url = (!id) ? undefined :
    _.isNumber(id) ? "/server/service/players/"+id+"/source" : id;

  this.create_worker();
};

Player.prototype.create_worker = function() {
  this.worker = new Worker(this.url);

  this.worker.onmessage = function(e) {
    var message = "on_" + e.data.message;
    var data = e.data.data || {};
    data.player_number = this.player_number;

    this[message] !== undefined ? this[message](data) : this.on_error("BUG: Player message not handled: " + message);
  }.bind(this);

  this.worker.onerror = this.on_error;
};
Player.prototype.on = function(msg, data) { this.arbiter.publish(msg, data); };
Player.prototype.on_log = _.partial(Player.prototype.on, "Log/Player");
Player.prototype.on_ready = _.partial(Player.prototype.on, "Player/Ready");
Player.prototype.on_move = _.partial(Player.prototype.on, "Player/Move");
Player.prototype.on_error = _.partial(Player.prototype.on, "Player/Error");

Player.prototype.sendMessage = function(message, data) { this.worker.postMessage({"message": message, "data": data}); };
Player.prototype.start = function(data) {
  this.player_number = data.player_number;
  this.sendMessage("start", data);
};
Player.prototype.move = _.partial(Player.prototype.sendMessage, "move");
Player.prototype.end = _.partial(Player.prototype.sendMessage, "end");
Player.prototype.match_end = _.partial(Player.prototype.sendMessage, "match_end");
Player.prototype.error = _.partial(Player.prototype.sendMessage, "error");
Player.prototype.source = _.partial(Player.prototype.sendMessage, "source");

// ========================================================================
// Game object
// ========================================================================
var Game = function (id, arbiter) {
  this.worker = undefined;
  this.arbiter = arbiter || Arbiter;
  this.url = (!id) ? undefined :
    (_.isNumber(+id) && !isNaN(+id)) ? "/server/service/games/"+id+"/source" : id;
  this.create_worker();
};

Game.prototype.create_worker = function() {
  this.worker = new Worker(this.url);
  this.worker.onmessage = function(e) {
    var message = "on_" + e.data.message;
    var data = e.data.data;

    this[message] !== undefined ? this[message](data) : this.on_error("GAME-API BUG: Game message not handled: " + message);
  }.bind(this);
};

Game.prototype.sendMessage = function(message, data) { this.worker.postMessage({"message": message, "data": data}); };
Game.prototype.start = _.partial(Game.prototype.sendMessage, "start");
Game.prototype.move = _.partial(Game.prototype.sendMessage, "move");
Game.prototype.end = _.partial(Game.prototype.sendMessage, "end");
Game.prototype.match_end = _.partial(Game.prototype.sendMessage, "match_end");
Game.prototype.error = _.partial(Game.prototype.sendMessage, "error");
Game.prototype.on = function(msg, data) { this.arbiter.publish(msg, data); };
Game.prototype.on_log = _.partial(Game.prototype.on, "Log/Game");
Game.prototype.on_render = _.partial(Game.prototype.on, "Game/Render");
Game.prototype.on_error = _.partial(Game.prototype.on, "Game/Error");
Game.prototype.on_get_move = _.partial(Game.prototype.on, "Game/Get_Move");
Game.prototype.on_end = function(data) {
  this.end();
  this.arbiter.publish("Game/End", data);
};
// ========================================================================
// Game Controller
// ========================================================================
var Match = function(game, players, config, arbiter) {
  "use strict";
  config = config || {};

  this.started = false;

  this.moving_players = [];
  this.waiting_on_players = {};
  this.moves_recieved = {};

  this.game=game || this.die("No game passed to start()");
  this.players=players || this.die("No players passed to start()");

  this.results = {}; //Results Container
  this.config = { // Container for match config
    total_games: 1,
    move_delay: 100,
    time_limit: 0
  };
  this.scenario = {};
  this.arbiter = arbiter || Arbiter;
  _.assign(this.config, config);

  this.get_move = this.arbiter.subscribe("Game/Get_Move",  {priority: 100}, function(data) {
    var player_numbers = _.isArray(data.player_number) ? data.player_number : [data.player_number];
    var move_data = data.data;

    var get_move = function() {
      this.moving_players = player_numbers;

      for(var i=0;i<player_numbers.length;i++) {
        var player_number = player_numbers[i];
          if(!this.config.time_limit || this.config.time_limit < 1) {
          this.waiting_on_players[player_number] = true;
        } else {
          this.waiting_on_players[player_number] = setTimeout(function() {
            this.players[player_number-1].error("Move was not received in time.");
            this.arbiter.publish("Player/Move", { player_number: player_number, move: null });
            this.log("Time limit exceeded for player " + player_number);
          }.bind(this), this.config.time_limit);
        }
      }

      for(var i=0;i<player_numbers.length;i++) {
          var player_number = player_numbers[i];
          var move__data = (_.isArray(move_data)) ? move_data[i] : move_data;
          this.players[player_number-1].move(move__data);
      }
    }.bind(this);

    if(!this.config.move_delay || this.config.move_delay === 0) {
      get_move();
    } else {
      setTimeout(get_move, this.config.move_delay);
    }
  }.bind(this));

  this.player_move = this.arbiter.subscribe("Player/Move",{priority: 100}, function(data) {
    //TODO why does this have game_state and data?
    if (this.waiting_on_players[data.player_number] === undefined) {// Player tried move out of turn
      this.log("ERROR: Player #"+data.player_number+" moved out of turn!");
    } else {
      clearTimeout(this.waiting_on_players[data.player_number]);
      delete this.waiting_on_players[data.player_number];

      if(this.moving_players.length !== 1) {
        this.moves_recieved[data.player_number] = data.move;
        if(Object.keys(this.waiting_on_players).length === 0) {
          data = {
            "player_number": Object.keys(this.moves_recieved),
            "move": this.moves_recieved
          };
          this.game.move(data);
          this.moving_players = [];
          this.moves_recieved = {};
        }
      } else {
        this.game.move(data);
        this.moving_players = [];
      }
    }
  }.bind(this));

  this.game_end = this.arbiter.subscribe("Game/End", {priority: 100}, function(data) {
    var winners = _.isArray(data.winner) ? data.winner : [data.winner];
    var message = data.message;

    _.each(winners, function(winner) {
      if (!winner || winner <= 0) {
        this.results.draws++;
      } else {
        this.results.player_wins[winner-1]++;
      }
    }, this);

    this.results.games_played++;
    this.results.current_game++;
    this.results.message = message;

    _.each(this.players, function(player, i) {
      player.end({
        "won": winners.indexOf(i+1) !== -1 ,
        "winner": ((winners.length > 1) ? winners : winners[0])
      });
    });

    if (this.results.current_game <= this.results.total_games) {
	    //Rotate the the players
	    _.rotateM(this.players);
	    _.rotateM(this.results.player_wins);

		  _.each(this.players, function(p) {
        p.player_number = p.player_number % this.players.length + 1;
      }, this);

      this.game.start(this.scenario);

    }
    else this.match_end();

    this.arbiter.publish("Match/Results", this.results);

  }.bind(this));

};
Match.prototype.start_game = function(scenario) {
	this.game.start(scenario);
	this.arbiter.publish("Game/Start",scenario);
};
Match.prototype.start = function(scenario) {
  if (this.started) return this.log("ERROR: start() called by a match is already in progress!");
  this.log("Starting Game");
  scenario = scenario || {};
  scenario.total_players = this.players.length;
  this.scenario = scenario;
  this.reset_results();

  //Ensure that all players are ready before starting the game
  var players_ready = 0;
  var subID = this.arbiter.subscribe("Player/Ready", {priority: 100}, function() {
    players_ready++;
    if(players_ready === this.players.length) {
      players_ready++;
      this.arbiter.unsubscribe(subID);
      this.start_game(scenario);
      this.started = true;
    }
  }.bind(this));

  _.each(this.players,function(player,i) {
    player.start({"player_number":(i+1),"config": this.config});
  }.bind(this));

  scenario = scenario || {};
  scenario.total_games = this.results.total_games;
  scenario.total_players = this.players.length;

};
Match.prototype.log = function(message) {
  this.arbiter.publish("Log/Match",
    typeof s === "object" ? JSON.stringify(message) : message);
}
Match.prototype.die = function(s) { this.log("DIE", s); throw new Error(s); }

Match.prototype.reset_results = function() {
  this.results = {
    total_games: this.config.total_games,
    games_played: 0,
    current_game: 1,
    player_wins: _.repeat(0, this.players.length),
    draws: 0
  };
  this.arbiter.publish("Match/Results", this.results);

};
Match.prototype.match_end = function() {
  _.each(this.players, function(player) {
    player.match_end(this.results);
  }, this);

  this.results.current_game = 0;
  this.started = false;

  this.arbiter.publish("Match/Results", this.results);
  this.arbiter.publish("Match/End", this.results);
  this.arbiter.unsubscribe(this.get_move);
  this.arbiter.unsubscribe(this.player_move);
  this.arbiter.unsubscribe(this.game_end);
};
Match.prototype.abort = function() {
  _.each(this.players, function(player) {
    player.match_end({});
  });this.results.current_game = 0;
  this.started = false;

  this.arbiter.publish("Match/Abort", this.results);
  this.arbiter.unsubscribe(this.get_move);
  this.arbiter.unsubscribe(this.player_move);
  this.arbiter.unsubscribe(this.game_end);
};

