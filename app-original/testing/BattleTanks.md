# Battle Tanks!

Battle Tanks is the first iteration of a series of increasingly difficult games where the player controls a tank on a battlefield. The war takes place with multiple tanks on a randomly generated (uniformly) 2-D grid where all remaining players make a move each turn.

------

## Game Overview

The game takes place on a 2-D grid with the origin in the upper left corner. The $x$-axis is horizontal and refereed to as the `x` property in message exchanges. The $y$-axis is vertical and corresponds to the `y` property on supported objects.

The world contains four different objects. They are `TANK`s, `ROCK`s, `STAR`s, and `RUIN`s. `TANK`s and `ROCK`s are impassible, but other players controls `TANK`s. When a player's tank losses its `health` it turns into `RUIN`s and becomes passable. This is the only way to know when a tank dies.  `STAR`s are the only collectible item help decide the winner in ties.

Each turn the remaining tanks receive the observable state and the player make a move. If there is only one tank remaining, it is the winner. If the number of turns decreases to zero, then the players with the most stars that are still alive win.

## Start Method

When the game is started it calls the `Player.prototype.start` method with the scenario data as the first parameter. This contains useful information such as the damage when a player is hit by fire and the starting hip points. Below is a full example of the scenario data.

    {
      "dim": [ [10, 20],  [11, 21] ], /* Min and max dimensions */
      "blocks": [10, 20], /* Min and max number of rocks/impassible objects */
      "stars": [10, 20], /* Min and max number of stars */
      "player_health": 2, /* Starting health of each player */
      "vision": 1.5, /* Tanks field of vision distance */
      "radar": 3, /* Tanks field of vision distance when using radar */
      "turns": 1.5, /* The number of turns in the game is 1.5 * x * y */
      "damage": 1, /* Damage a tank receives when hit */
      "total_players": 4, /* Number of player */
      "total_games": 1 /* Number of games */
    };

## Tank Actions

Each turn a player receives a structure similar to the one below. `success` is true if the previous turns move did not fail. Each move has its own definition of success defined below. `x` and `y` are the tanks current location starting from $(0, 0)$ in the upper-left hand corner. `dim_x` and `dim_y` are the largest `x` and `y` values respectively. `turns_remaining` is the number of turns remaining before a star victory occurs. `percepts` are items in the tank's field of view and `events` contains occurrences on the battlefield that the tank observed, but are not necessarily in the field of view.

    {
      success: boolean,
      x: integer,
      y: integer,
      dim_x: integer,
      dim_y: integer,
      turns_remaining: integer,
      percepts: [{
        x: integer,
        y: integer,
        type: String
      }],
      events: [{
        type: String
      }]
    }

### Moves

`MOVE-NORTH`, `MOVE-EAST`, `MOVE-SOUTH`, `MOVE-WEST` are the four moves to control movement of the tank. These moves orient the tank in the direction of movement and move ahead one square. This move will always succeed unless there in an impassible object in front of the player. Impassible objects are `ROCK`s, `TANK`s, and out of bounds. In addition, if two tanks attempt to move into the same square, both moves fail and their positions remain unchanged. Players are allowed to "swap" positions with tanks (e.g the red tank is at position $(4,5)$ and invokes `move("MOVE-DOWN")` the blue tank is at $(4,6)$ and invokes `move("MOVE-UP")`.

#### Fire

The `FIRE-NORTH`, `FIRE-EAST`, `FIRE-SOUTH`, and `FIRE-WEST`  rotates the tank to face the correct direction and then fires the mount. The line of fire is a straight line from the tank to the first impassible object (i.e. `ROCK`s and `TANK`s). If a player is hit, the move is successful and the hit player takes damage; otherwise it fails. Firing the tank's mount reveals your tanks location to all players.

#### Radar

Radar increases your field of view. All items in your field of view are in the `percepts` array. Each element has a `type` which can take values `TANK`, `ROCK`, `STAR`, or `RUINS`. This move cannot fail and an example of each type is below.

    [
      {"x":7,"y":4,"type":"ROCK"},
      {"x":7,"y":5,"type":"STAR"},
      {"x":6,"y":7,"type":"TANK","health":2,"stars":0,"direction":"SOUTH"},
      {"x":4,"y":18,"type":"RUINS","player":3}
    ]

#### Hold

Holding does not move or rotate the tank. This cannot fail.

### Events

There are three diifferent events that can take place. The first is collecting a star. The other two are when an opponent fires and when an opponent hits your tank. The ones related to a tank firing have `from` properties specifying their location.

    [
    	{"type": "STAR"}, /* Collected a star */
    	{"type":"FIRE","from": {x: 16, y: 15}}, /* tank fired from location (16, 15) */
    	{"type":"HIT","from": { x: 12, y: 1}} /* tank at (12, 1) damaged your tank */
	]