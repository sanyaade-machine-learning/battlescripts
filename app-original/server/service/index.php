<?php

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Actions\PlayerActions;

require_once '../vendor/autoload.php';
require_once '../classes/BattleBroker.php';
require_once $_SERVER["DOCUMENT_ROOT"].'/server/UtilityFunctions.php';


ini_set("display_errors", 0);
class Service{
    public function __construct($app){
    	//Check to see if the user is Authenticated
    	//if(!isset($_SESSION['user']) || $_SESSION['user']['id']<1)
    		// redirect him back to login page
    		//header( "Location: /index.php?error=Your are not connected or your session has expired" );
        $this->process($app);
    }
    public function process(Application $app){

        $broker = new BattleBroker();

        //Convert JSON, if necessary

		$app->before(function (Request $request) {
			if (strpos($request->headers->get('Content-Type'), 'application/json')!==false) {
				$data = json_decode($request->getContent(),true);
				$request->request->replace(is_array($data)?$data:array());
			}

		});

		// Handle errors by returning the exception text
		$app->error(function (\Exception $e, $code) use ($app) {
			// logic to handle the error and return a Response
			if ($app['debug']) {
				return $e->getMessage();
			}
			else {
				return "An error has occurred";
			}
		});

        // Required for CORS
		// See: http://stackoverflow.com/questions/19409105/cors-preflight-request-returning-http-405
		$app->match("{url}", function($url) use ($app) { return "OK"; })->assert('url', '.*')->method("OPTIONS");

		$app->get('/devl','GameActions::retrieveGames');
		$app->get('/devl/{id}','GameActions::retrieveGame');

        /**** Game Routes *****/
        $app->get('/games', 'GameActions::retrieveGames');
		$app->post('/games', 'GameActions::createGame');
        $app->get('/games/{id}','GameActions::retrieveGame');
        $app->post('/games/{id}','GameActions::updateGame');
        $app->get('/games/{id}/source','GameActions::retrieveGameSource');
        $app->get('/games/{id}/players','PlayerActions::retrievePlayersForGame' );
        $app->get('/games/{id}/myplayers','PlayerActions::retrieveMyPlayers');
        $app->get('/games/{id}/templateSource','GameActions::retrieveTemplateSource');
        /**** Player Routes ***/
		$app->post('/players',"PlayerActions::createPlayer");
		$app->get('/players/{playerId}','PlayerActions::retrievePlayer');
        $app->get('/players/{id}/source','PlayerActions::retrievePlayerSource');
		$app->post('/players/{playerId}', 'PlayerActions::updatePlayer');
		$app->delete('/players/{playerId}','PlayerActions::removePlayer');
		$app->post('/players/{playerId}/publish', 'PlayerActions::publishPlayer');

	$app->run();

    }//eof process()
}//eof class definition

$app = new Silex\Application();
$app['debug'] = true;
$service = new Service($app);



?>