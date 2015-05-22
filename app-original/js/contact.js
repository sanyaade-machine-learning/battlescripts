var app = angular.module('myapp',[]);
app.controller('contactCntrl',['$scope', '$http', function($scope, $http){
	$scope.contactSend = function(){
		 $http({
	            url: '/mailContact.php',
	            method: "POST",
	            data: $scope.data,
	            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
	        }).success(function (data, status, headers, config) {
	        		$scope.saveSuccessful = true;
	        		$scope.data.name='';
	        		$scope.data.email='';
	        		$scope.data.messagetext = '';
	            }).error(function (data, status, headers, config) {
	                $scope.status = status;
	            });
	}
}]);