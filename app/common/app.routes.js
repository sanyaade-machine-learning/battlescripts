( function() {
  'use strict';

  angular.module( 'battle' )
    .config( function( $stateProvider, $urlRouterProvider ) {
      $stateProvider
        .state( 'root', {
          abstract: true,
          template: '<battle-app></battle-app>'
        } )
        .state( 'root.promo', {
          url: '/',
          template: '<promo></promo>'
        } );


      $urlRouterProvider.otherwise( '/' );
    } );

} )();
