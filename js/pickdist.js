define(['modules/pickdistgraph', 'modules/analyticsunsupported', 'modernizr', 'common'], function(pickdist, analyticsunsupported) {
	if (Modernizr.inlinesvg) {
		pickdist('#graph', '#graphControls');
	} else {
		require(['jquery'], function() {
			$('div.navTabs').remove();
			analyticsunsupported('#graph');
		});
	}
});
