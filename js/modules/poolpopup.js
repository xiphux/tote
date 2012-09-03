define(['jquery', 'qtip'], function($) {

	return function(link, action, title) {

		var url = window.location.href.match(/^([^\?]+\/)/);
		if (!url) {
			return;
		}
		url = url[1];

		var lnkHistory = $(link);
		var poolid = lnkHistory.attr('href').match(/p=([0-9a-fA-F]+)/);
		if (!poolid) {
			return;
		}
		poolid = poolid[1];

		lnkHistory.qtip({
			content: {
				text: '<img src="' + url + 'images/editpool-loader.gif" alt="Loading..." />',
				ajax: {
					url: 'index.php',
					data: {
						a: action,
						p: poolid,
						o: 'js'
					},
					type: 'GET',
					once: false
				},
				title: {
					text: title,
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

		lnkHistory.click(function() {
			return false;
		});

	}
});
