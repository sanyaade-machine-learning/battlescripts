var player = null;
var game_state = null; // Temporary place-holder to store the game state passed to the player's move() method
self.addEventListener('message', function(e) { 
    var msg = e.data.message;
    var data = e.data.data;
    if ("source"==msg && typeof player[data]=="function") {
        send("source",player[data].toString());
    }
    // Store the move data?
    else if ("move"===msg) {
        game_state = data;
    }
    else if ("start" === msg) {
      player = new Player();
      send("ready", player.start ? player.start(data) : true);
    }
    if (typeof player[msg]=="function") {
        player[msg](data);
    }
},false);
var send = function(msg,data) {
    postMessage({"message":msg,"data":data});
};
var move = function(move,data) {
    // Player has moved, pass back the game state
    send("move",{"move":move,"data":data,"game_state":game_state});
    return move;
};
var log = function(msg) {
    send("log",{"message":msg});
};
