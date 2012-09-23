define(['modules/pickdistgraph', 'modules/analyticsunsupported', 'modernizr', 'd3', 'common'], function(pickdist, analyticsunsupported) {
	if (Modernizr.inlinesvg) {
		pickdist('#graph', '#graphControls');
	} else {
		d3.select('div.navTabs').remove();
		analyticsunsupported('#graph');
	}
});
