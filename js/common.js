define(['module', 'modernizr'], function(module) {
	var isMobile = module.config().mobile;
	if (isMobile) {
		require(['modules/mobilehideaddressbar']);
	}
	if (!Modernizr.input.autofocus) {
		require(['jquery'], function($) {
			$('input[autofocus]').filter(':first').focus();
		});
	}
});
