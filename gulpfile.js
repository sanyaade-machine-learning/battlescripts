'use strict';

var setup = require( 'front-end-work-flow/gulp-tasks/setup' );

var removeJsStyles = function( arr ) {
  return arr.filter( function( task ) {
    return task !== 'js-styles';
  } );
};

// Remove js-style from manticore-sting
setup.aggTaskDefinitions[ 'manticore-sting' ].dependencies =
  removeJsStyles( setup.aggTaskDefinitions[ 'manticore-sting' ].dependencies );

// Remove js-styles from all watches
setup.aggTaskDefinitions[ 'medusa-gaze' ].watches =
  setup.aggTaskDefinitions[ 'medusa-gaze' ].watches.map( function( obj ) {
    obj.tasks = removeJsStyles( obj.tasks );
    return obj;
  } );

var few = require( 'front-end-work-flow/gulpfile.js');

few.angular.module = 'battle';
few.globals = {
  angular: false
};
few.files = {
  css: [ 'app/style.less' ],
  browser: [ 'app/**/*.js', '!app/**/*.spec.js' ],
  node: [ 'gulpfile.js', 'server.js' ],
  html: [ 'app/**/*.html' ],
  json: [ 'package.json' ],
  unit: [
    'node_modules/angular-mocks/angular-mocks.js',
    'app/**/*.test.js'
  ],
};

few.files.devLibraries = {
  '/less.js': 'node_modules/less/dist/less.js'
};

few.files.libraries = {
  '/normalize.css/normalize.css': 'node_modules/normalize.css/normalize.css',
  '/angular.js': 'node_modules/angular/angular.js',
  '/angular-ui-router.js':
    'node_modules/angular-ui-router/release/angular-ui-router.js'
};
