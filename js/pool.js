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
				classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
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
			classes: 'ui-tooltip-light ui-tooltip-shadow ui-tooltip-rounded'
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

function initRulesDisplay() {
	var url = window.location.href.match(/^([^\?]+\/)/);
        if (!url) {
                return;
        }
	url = url[1];
	$('a#lnkRules').qtip({
		content: {
			text: '<img src="' + url + 'images/editpool-loader.gif" alt="Loading..." />',
			ajax: {
				url: 'index.php',
				data: {
					a: 'rules'
				},
				type: 'GET'
			},
			title: {
				text: 'Pool Rules',
				button: true
			}
		},
		position: {
			my: 'center',
			at: 'center',
			target: $(window)
		},
		show: {
			event: 'click',
			solo: true,
			modal: true
		},
		hide: false,
		style: {
			classes: 'ui-tooltip-light ui-tooltip-modal ui-tooltip-rounded rulesDialog'
		}
	});

	$('a#lnkRules').click(function() {
		return false;
	});
};

function initHistoryDisplay() {
	var url = window.location.href.match(/^([^\?]+\/)/);
        if (!url) {
                return;
        }
	url = url[1];

	var lnkHistory = $('a#lnkHistory');
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
					a: 'history',
					p: poolid,
					o: 'js'
				},
				type: 'GET',
				once: false
			},
			title: {
				text: 'Pool History',
				button: true
			}
		},
		position: {
			my: 'center',
			at: 'center',
			target: $(window)
		},
		show: {
			event: 'click',
			solo: true,
			modal: true
		},
		hide: false,
		style: {
			classes: 'ui-tooltip-light ui-tooltip-modal ui-tooltip-rounded historyDialog'
		}
	});

	lnkHistory.click(function() {
		return false;
	});
};

function initLinksList() {
	$('#linksList').hide();

	var link = jQuery(document.createElement('a'));
	link.attr('href', '#');
	link.attr('id', 'lnkLinks');
	link.text('Show links...');
	link.click(function() {
		if ($('#linksList').is(':visible')) {
			$('#linksList').hide('fast');
			$(this).text('Show links...');
		} else {
			$('#linksList').show('fast', function() {
				$('html,body').animate({scrollTop: $('body').attr('scrollHeight')}, 500);
			});
			$(this).text('Hide links...');
		}
		return false;
	});
	$('#spanLinks').replaceWith(link);
};

$(document).ready(function() {
	initPoolTips();
	initFeedTips();
	initPoolNav();
	initRulesDisplay();
	initHistoryDisplay();
	initLinksList();
});
