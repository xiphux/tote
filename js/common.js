define(['jquery', 'module'], function($, module) {
	var isMobile = module.config().mobile;
	if (isMobile) {
		require(['modules/mobilehideaddressbar']);
	}
	$('.initialFocus').focus();
});
