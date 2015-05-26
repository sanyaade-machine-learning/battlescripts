( function() {
  'use strict';

  var app = angular.module( 'battle', [ 'ui.router' ] );

  angular.element( document ).ready( function() {
    angular.bootstrap( document, [ 'battle' ] );
  } );

  app.constant( 'APP_VERSION', '0.0.0' );

} )();
