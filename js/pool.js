define(['jquery', 'cs!modules/autoselectnav', 'cs!modules/poolpopup', 'cs!modules/feedtip', 'cs!modules/schedulepopup', 'cs!modules/collapsiblesection', 'cs!modules/scoreticker/scoreticker', 'cs!modules/titletips', 'common'], function($, autoselectnav, poolpopup, feedtip, schedulepopup, collapsiblesection, ScoreTicker) {
	autoselectnav('#poolNameSelect', '#poolNameSubmit');

	poolpopup('a#lnkHistory', 'history', 'Pool History');
	poolpopup('a#lnkRules', 'rules', 'Pool Rules');
	feedtip('a.feedTip');
	schedulepopup('.scheduleLink');

  collapsiblesection('#linksSection', 'ToteLinksExpanded');

	var ticker = new ScoreTicker('#scoreTicker');
	ticker.initialize();
});
