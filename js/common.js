define(['module', 'cs!modules/mobilehideaddressbar', 'modernizr'], function(module, mobilehideaddressbar) {
	var isMobile = module.config().mobile;
	if (isMobile) {
		mobilehideaddressbar();
	}
	if (!Modernizr.input.autofocus) {
		require(['jquery'], function($) {
			$(document).ready(function() {
				$('input[autofocus]').filter(':first').focus();
			});
		});
	}
});
