define(['modules/teamrelgraph', 'modules/analyticsunsupported', 'modernizr', 'common'], function(teamrel, analyticsunsupported) {
	if (Modernizr.inlinesvg) {
		teamrel('#graph', '#graphControls');
	} else {
		require(['jquery'], function() {
			$('div.navTabs').remove();
			analyticsunsupported('#graph');
		});
	}
});
