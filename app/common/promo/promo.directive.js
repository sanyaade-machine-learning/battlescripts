( function() {
  'use strict';

  angular.module( 'battle' )
    .directive( 'promo', function() {
      return {
        restrict: 'E',
        templateUrl: '/common/promo/promo.view.html'
      };
    } );

} )();
