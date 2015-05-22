self.addEventListener('message', function(e) {
    var msg = e.data.message;
    var data = e.data.data;
    if (msg=="start") {
        game.start(data);
    }
    else if (msg=="move") {
        game.move(data.player_number,data.move,data.move_data);
    }
    else if (typeof game[msg]=="function") {
        game[msg](data);
    }
},false);
function send(msg,data) {
    postMessage({"message":msg,"data":data});
};
function get_move(player_number,data) {
    send("get_move",{"player_number":player_number,"data":data});
};
function render(data) {
    send("render",data);
};
function log(msg) {
    send("log",{"message":msg});
};
function start_players(data) {
    send("start_players",data);
};
function end(winner,message) {
    send("end", {"winner":winner,"message":message} );
};

// Prototype Game to inherit from
function GamePrototype() {
    this.first_player = 1;
    this.current_player = 1;
    this.total_players = 1;

    this.next_player = function() {
        this.current_player++;
        if (this.current_player > this.total_players) {
            this.current_player = 1;
        }
    };

    this.increment_first_player = function() {
        this.first_player++;
        if (this.first_player > this.total_players) {
            this.first_player = 1;
        }
    };
};

// Util functions
// ==============

// Build an array of objects
function fill_array(num,o) {
    var a = [];
    for (var i=0; i<num; i++ ) {
        if (typeof o=="object") {
            a[i] = JSON.parse(JSON.stringify(o));
        }
        else {
            a[i] = o;
        }
    }
    return a;
}

// Set the prototype and instantiate the Game
// ==========================================
Game.prototype = new GamePrototype();
var game = new Game();

