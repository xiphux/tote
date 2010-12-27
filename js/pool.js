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
				classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded'
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
			classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded'
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
	
	var lnkRules = $('a#lnkRules');
	var poolid = lnkRules.attr('href').match(/p=([0-9a-fA-F]+)/);
	if (!poolid) {
		return;
	}
	poolid = poolid[1];

	lnkRules.qtip({
		content: {
			text: '<img src="' + url + 'images/editpool-loader.gif" alt="Loading..." />',
			ajax: {
				url: 'index.php',
				data: {
					a: 'rules',
					p: poolid,
					o: 'js'
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
			classes: 'ui-tooltip-tote ui-tooltip-modal ui-tooltip-rounded rulesDialog'
		}
	});

	lnkRules.click(function() {
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
			classes: 'ui-tooltip-tote ui-tooltip-modal ui-tooltip-rounded historyDialog'
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
	var showText = 'Useful links…';
	var hideText = 'Useful links';
	link.text(showText);
	link.addClass('linksClosed');
	link.click(function() {
		if ($('#linksList').is(':visible')) {
			$('#linksList').hide('fast');
			$(this).text(showText);
			$(this).removeClass('linksOpen');
			$(this).addClass('linksClosed');
		} else {
			$('#linksList').show('fast', function() {
				$('html,body').animate({scrollTop: $('body').attr('scrollHeight')}, 500);
			});
			$(this).text(hideText);
			$(this).removeClass('linksClosed');
			$(this).addClass('linksOpen');
		}
		return false;
	});
	$('#spanLinks').replaceWith(link);
};

function initScheduleLinks() {
	var url = window.location.href.match(/^([^\?]+\/)/);
        if (!url) {
                return;
        }
	url = url[1];

	$('.scheduleLink').each(function() {
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
				target: $(window)
			},
			show: {
				event: 'click',
				solo: true,
				modal: true
			},
			hide: false,
			style: {
				classes: 'ui-tooltip-tote ui-tooltip-modal ui-tooltip-rounded'
			}
		});

		$(this).click(function() {
			return false;
		});
	});

};

$(document).ready(function() {
	initPoolTips();
	initFeedTips();
	initPoolNav();
	initRulesDisplay();
	initHistoryDisplay();
	initLinksList();
	initScheduleLinks();
});
