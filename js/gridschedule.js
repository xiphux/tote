function initSeasonNav() {
	$('#seasonSelect').change(function() {
		$(this).parent().submit();
	});
	$('#seasonSubmit').remove();
};

function initGridTips() {
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
};

$(document).ready(function() {
	initSeasonNav();
	initGridTips();
});
