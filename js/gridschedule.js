define(['jquery', 'modules/autoselectnav', 'qtip', 'common'], function($, autoselectnav) {
	autoselectnav('#seasonSelect', '#seasonSubmit');

	$('.gridSchedule .gridGame').qtip({
		style: {
			classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded'
		},
		position: {
			viewport: $(window),
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
});
