<?php
class Player {
	public $id;
	public $gameId;
	public $owner;//User
	public $name;
	public $description;
	public $version;
	public $source;
	public $publishedSource;
	public $publishedOn;
	public $defaultTemplateFlag='N';
	public $defaultOpponentFlag='N';
	public $testOpponentFlag='N';
	public $updatedOn;
}
?>