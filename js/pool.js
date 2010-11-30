function initFeedTips() {
	$('a.feedTip').each(function()
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
				classes: 'ui-tooltip-light ui-tooltip-shadow'
			},
			position: {
				adjust: {
					screen: true
				}
			}
		});

		$(this).click(function() { return false; });
	});
}

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

function initPoolNav() {
	$('#poolNameSelect').change(function() {
		$(this).parent().submit();
	});
	$('#poolNameSubmit').remove();
};

$(document).ready(function() {
	initPoolTips();
	initFeedTips();
	initPoolNav();
});
