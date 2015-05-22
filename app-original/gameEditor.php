<!DOCTYPE html>
<?php
    require_once './server/vendor/autoload.php';
require_once './server/classes/BattleBroker.php';
require_once './server/classes/Game.php';

    if(isset($_POST["id"])) {
        $fields = array("name", "synopsis", "description", "private", "source", "rules", "canvas", "minPlayers", "maxPlayers", "version");
        $game = new game();
        $game->id = $_POST["id"];
        foreach($fields as $f) {
            $game->$f=$_POST[$f];
        }
        $broker = new BattleBroker();
        $broker->updateGame($game);
    }
?>
<!DOCTYPE html>
<html ng-app>
<head lang="en">
    <meta charset="UTF-8">
    <title></title>
  <link rel="stylesheet" href="./lib/codemirror-3.20/lib/codemirror.css" />
  <link rel="stylesheet" href="./lib/codemirror-3.20/theme/mdn-like.css" />
</head>
<body ng-controller="myController">
<form method="post" action="gameEditor.php">
  <div>Game Id: <input ng-model="id" name="id" ng-change="get(id)"/></div>
  <div ng-repeat="field in schema">
    <label>{{field}}</label>
    <div ng-switch="['source', 'rules', 'canvas'].indexOf(field) === -1">
      <input name="{{field}}" ng-switch-when="true" ng-model="data[field]" />
      <textarea name="{{field}}" ng-switch-when="false" ng-model="data[field]" ></textarea>
    </div>
  </div>
  <input type="submit" />
</form>
  <script type="text/javascript">
    var myController = function($scope, $http) {
      $scope.schema = [ "name", "synopsis", "description", "private", "source", "rules", "canvas", "minPlayers", "maxPlayers", "version" ];
      $scope.data = {};

      $scope.id = <?php echo isset($_POST["id"]) ? $_POST["id"] : "undefined"?>;

      $scope.get = function(id) {
        $http.get("/server/service/games/" + id).then(function(data) {
          $scope.data = data.data;
        });
      };

      if($scope.id) $scope.get($scope.id);

    };
  </script>
  <script type="text/javascript" src="./js/lodash.js"></script>
  <script type="text/javascript" src="./js/angular.js"></script>
  <script type="text/javascript" src="./js/jquery.js"></script>
  <script type="text/javascript" src="./lib/codemirror-3.20/lib/codemirror.js"></script>
  <script type="text/javascript" src="./lib/codemirror-3.20/mode/javascript/javascript.js"></script>
</body>
</html>
