define(['modules/teamrelgraph', 'modules/analyticsunsupported', 'modernizr', 'd3', 'common'], function(teamrel, analyticsunsupported) {
	if (Modernizr.inlinesvg) {
		teamrel('#graph', '#graphControls');
	} else {
		d3.select('div.navTabs').remove();
		analyticsunsupported('#graph');
	}
});
