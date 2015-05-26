beforeEach( module( 'battle' ) );

describe( 'Battle', function() {

  it( 'APP_VERSION', inject( function( APP_VERSION ) {
    expect( APP_VERSION ).toBeTruthy();
  } ) );

} );
