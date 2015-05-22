<?php
class Game {
	public $id;
	public $name;
	public $description;
	public $private=false;
	public $synopsis;
	public $source;
	public $rules;
	public $canvas;
	public $minPlayers=1;
	public $maxPlayers=2;
	public $version;
	public $createdBy; //User
	public $options=array();//Array(Option)
	public $scenarios=array();//Array(Scenario)
	public $difficultyRating;
	public $icon;
	public $screenshot;
	public $updatedOn;
}
?>