/*!
 * jQuery fontAwesome-powered simple input[type="search"] polyfill
 * https://github.com/mattstauffer/jQuery-fontAwesome-SearchPolyfill
 * Original author: Matt Stauffer, github.com/mattstauffer (@stauffermatt)
 * Plugin pattern by @addyosmani: https://github.com/addyosmani/jquery-plugin-patterns/
 */

;(function ( $, window, document, undefined ) {

	var pluginName = 'fontAwesomeSearchPolyfill',
		defaults = {
			theme: 'none'
		};

	// Plugin Constructor
	function Plugin( element, options ) {
		this.element = element;

		this.options = $.extend( {}, defaults, options) ;
		
		this._defaults = defaults;
		this._name = pluginName;
		
		this.init();
	}

	// Our plugin
	Plugin.prototype.init = function () {
		var $cancel_button,
			$el = $(this.element);

		// Totally bootleg: override default search styles
		$("<style type='text/css'>input[type=\"search\"] { -webkit-appearance: textfield; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box; } input[type=\"search\"]::-webkit-search-decoration, input[type=\"search\"]::-webkit-search-cancel-button { -webkit-appearance: none; }</style>").appendTo("head");

		// Some theme ideas: Webkit, Google, Slight round, etc.
		// @todo: Make this a sexier, more modular system
		switch( this.options.theme ) {
			// @todo: Actually make this work.
		}

		// Do the work.
		$cancel_button = $('<a href="#" class="icon-remove-sign" />').css('display', 'none');

		$cancel_button.on( 'click', function( e ) {
			e.preventDefault();
			$el.val('');
			$el.trigger('change');
			$el.trigger('keyup.DT'); // rex: for jQuery.dataTables support
			$cancel_button.hide();
		});

		$el
			//.wrap('<span class="search-input-wrapper" />')
			//.before('<i class="icon-search" />')
			.wrap('<div class="input-prepend" />') // rex: for Twitter Bootstrap support
			.before('<span class="add-on"><i class="icon-search"></i></span>') // rex: for Twitter Bootstrap support
			.after( $cancel_button )
			.on( 'keyup', function() {
				// Manage search cancel button
				if( $el.val() !== '' ) {
					$cancel_button.show();
				} else {
					$cancel_button.hide();
				}
			});
	};

	$.fn[pluginName] = function ( options ) {
		return this.each(function () {
			if (!$.data(this, 'plugin_' + pluginName)) {
				$.data(this, 'plugin_' + pluginName, 
				new Plugin( this, options ));
			}
		});
	};

})( jQuery, window, document );
