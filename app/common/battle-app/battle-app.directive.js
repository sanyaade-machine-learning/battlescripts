( function() {
  'use strict';

  angular.module( 'battle' )
    .directive( 'battleApp', function() {
      return {
        restrict: 'E',
        templateUrl: '/common/battle-app/battle-app.view.html'
      };
    } );

} )();
