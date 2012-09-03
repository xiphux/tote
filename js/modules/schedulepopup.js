define(['jquery', 'qtip'], function($) {
	return function(schedulelink) {
		var url = window.location.href.match(/^([^\?]+\/)/);
		if (!url) {
			return;
		}
		url = url[1];

		$(schedulelink).each(function() {
			var season = $(this).attr('href').match(/y=([0-9]+)/);
			if (!season) {
				return;
			}
			season = season[1];
		
			var week = $(this).attr('href').match(/w=([0-9]+)/);
			if (!week) {
				return;
			}
			week = week[1];
			
			$(this).removeData('qtip');

			$(this).qtip({
				content: {
					text: '<img src="' + url + 'images/editpool-loader.gif" alt="Loading..." />',
					ajax: {
						url: 'index.php',
						data: {
							a: 'schedule',
							y: season,
							w: week,
							o: 'js'
						},
						type: 'GET',
						once: false
					},
					title: {
						text: season + '-' + (season*1+1) + ' week ' + week + ' schedule',
						button: true
					}
				},
				position: {
					my: 'center',
					at: 'center',
					target: $(window),
					effect: false
				},
				show: {
					event: 'click',
					solo: true,
					modal: true
				},
				hide: false,
				style: {
					classes: 'ui-tooltip-tote ui-tooltip-modal ui-tooltip-rounded totePopup',
					def: false
				}
			});

			$(this).click(function() {
				return false;
			});
		});
	};
});
