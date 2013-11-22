describe( 'Modal module', function() {
	'use strict';

	var browserDetect = {},
		modal = modules[ 'wikia.ui.modal' ]( jQuery, window, browserDetect );

	it( 'registers AMD module', function() {
		expect( modal ).toBeDefined();
		expect( typeof modal ).toBe( 'object' );
	} );

});

describe( 'Modal events', function() {
	'use strict';

	var browserDetect = {},
		module = modules[ 'wikia.ui.modal' ]( jQuery, window, browserDetect),
		modal = null;

	beforeEach( function() {
		modal = module.init( 'test' );
	} );

	it( 'triggers the event listener exactly once', function() {
		var listeners = {
			onFoo : function () { }
		};
		spyOn( listeners, 'onFoo' );

		modal.bind( 'foo', listeners.onFoo );
		modal.trigger( 'foo' );

		expect( listeners.onFoo ).toHaveBeenCalled();
		expect( listeners.onFoo.calls.length ).toEqual( 1 );
	} );

	it( 'triggers the proper event listener', function() {
		var listeners = {
			onFoo : function () { },
			onBar : function () { }
		};
		spyOn( listeners, 'onFoo' );
		spyOn( listeners, 'onBar' );

		modal.bind( 'foo', listeners.onFoo );
		modal.bind( 'bar', listeners.onBar );
		modal.trigger( 'foo' );
		modal.trigger( 'foo' );

		expect( listeners.onFoo ).toHaveBeenCalled();
		expect( listeners.onFoo.calls.length ).toEqual( 2 );
		expect( listeners.onBar ).not.toHaveBeenCalled();
	} );

	it( 'triggers all event listeners', function() {
		var listeners = {
			onFoo1 : function () { },
			onFoo2 : function () { }
		};
		spyOn( listeners, 'onFoo1' );
		spyOn( listeners, 'onFoo2' );

		modal.bind( 'foo', listeners.onFoo1 );
		modal.bind( 'foo', listeners.onFoo2 );
		modal.trigger( 'foo' );

		expect( listeners.onFoo1 ).toHaveBeenCalled();
		expect( listeners.onFoo1.calls.length ).toEqual( 1 );
		expect( listeners.onFoo2 ).toHaveBeenCalled();
		expect( listeners.onFoo2.calls.length ).toEqual( 1 );
	} );

	it( 'triggers event listeners in order', function() {
		var array = [],
			listeners = {
			onFoo1 : function () {
				array.push( 'foo1' );
			},
			onFoo2 : function () {
				array.push( 'foo2' );
			}
		};

		modal.bind( 'foo', listeners.onFoo1 );
		modal.bind( 'foo', listeners.onFoo2 );
		modal.trigger( 'foo' );

		expect( array ).toEqual( [ 'foo1', 'foo2' ] );
	} );

});
