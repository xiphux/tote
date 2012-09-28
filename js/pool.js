define(['jquery', 'cs!modules/autoselectnav', 'modules/poolpopup', 'modules/feedtip', 'modules/schedulepopup', 'modules/scoreticker/scoreticker', 'modules/titletips', 'cookies', 'common'], function($, autoselectnav, poolpopup, feedtip, schedulepopup, ScoreTicker) {
	autoselectnav('#poolNameSelect', '#poolNameSubmit');

	poolpopup('a#lnkHistory', 'history', 'Pool History');
	poolpopup('a#lnkRules', 'rules', 'Pool Rules');
	feedtip('a.feedTip');
	schedulepopup('.scheduleLink');


	$('#linksList').hide();

	var link = jQuery(document.createElement('a'));
	link.attr('href', '#');
	link.attr('id', 'lnkLinks');
	var showText = 'Useful links...';
	var hideText = 'Useful links';
	var visible = false;
	if ($.cookies.test()) {
		var ck = $.cookies.get('ToteLinksExpanded');
		if (ck !== null) {
			visible = ck;
		}
	}
	if (visible) {
		link.text(hideText);
		link.addClass('linksOpen');
		$('#linksList').show();
	} else {
		link.text(showText);
		link.addClass('linksClosed');
		$('#linksList').hide();
	}
	link.click(function() {
		if ($('#linksList').is(':visible')) {
			$('#linksList').hide('fast');
			$(this).text(showText);
			$(this).removeClass('linksOpen');
			$(this).addClass('linksClosed');
			if ($.cookies.test()) {
				var exp = new Date();
				exp.setDate(exp.getDate() + 365);
				$.cookies.set('ToteLinksExpanded', false, {expiresAt: exp});
			}
		} else {
			$('#linksList').show('fast', function() {
				$('html,body').animate({scrollTop: $('body').attr('scrollHeight')}, 500);
			});
			$(this).text(hideText);
			$(this).removeClass('linksClosed');
			$(this).addClass('linksOpen');
			if ($.cookies.test()) {
				var exp = new Date();
				exp.setDate(exp.getDate() + 365);
				$.cookies.set('ToteLinksExpanded', true, {expiresAt: exp});
			}
		}
		return false;
	});
	$('#spanLinks').replaceWith(link);

	var ticker = new ScoreTicker('#scoreTicker');
	ticker.initialize();
});
