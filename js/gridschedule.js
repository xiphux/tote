function initSeasonNav() {
	$('#seasonSelect').change(function() {
		$(this).parent().submit();
	});
	$('#seasonSubmit').remove();
};

function initGridTips() {
	$('.gridSchedule .gridGame').qtip({
		style: {
			classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded'
		},
		position: {
			adjust: {
				screen: true
			},
			my: 'bottom left',
			at: 'top center'
		},
		content: {
			text: function(api) {
				var tipcontent = $(document.createElement('div'));
				tipcontent.css('text-align', 'center');
				tipcontent.html($(this).attr('data-game') + "<br />" + $(this).attr('data-start'));
				return tipcontent;
			}
		}
	});
};

$(document).ready(function() {
	initSeasonNav();
	initGridTips();
});
