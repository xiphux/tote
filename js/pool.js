function initPoolTips() {
	$('*[title]').qtip({
		style: {
			classes: 'ui-tooltip-light ui-tooltip-shadow'
		},
		position: {
			adjust: {
				screen: true
			}
		}
	});
};

$(document).ready(function() {
	initPoolTips();
});
