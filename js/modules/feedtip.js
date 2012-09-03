define(['jquery', 'qtip'], function($) {

	return function(feedlink) {

		$(feedlink).each(function()
		{
			var poolid = $(this).attr('href').match(/p=([^&]+)/);
			if (!poolid) {
				return;
			}
			poolid = unescape(poolid[1]);
			var content = '<div>Action feed format: ';
			content += '<a href="index.php?a=atom&p=' + poolid + '">Atom</a>';
			content += ' | ';
			content += '<a href="index.php?a=rss&p=' + poolid + '">RSS</a>';
			$(this).qtip(
			{
				content: {
					text: content
				},
				show: {
					event: 'click'
				},
				hide: {
					fixed: true,
					delay: 150
				},
				style: {
					classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded',
					def: false
				},
				position: {
					viewport: $(window),
					effect: false
				}
			});

			$(this).click(function() { return false; });
		});

	};

});
