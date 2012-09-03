define(['jquery', 'qtip'], function($) {
	$('*[title]').qtip({
		style: {
			classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded'
		},
		position: {
			adjust: {
				screen: true
			},
			my: 'bottom left',
			at: 'top center'
		}
	});
});
