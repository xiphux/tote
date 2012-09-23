define(['modules/pickriskgraph', 'modules/analyticsunsupported', 'modernizr', 'd3', 'common'], function(pickrisk, analyticsunsupported) {
	if (Modernizr.inlinesvg) {
		pickrisk('#graph', '#graphControls');
	} else {
		d3.select('div.navTabs').remove();
		analyticsunsupported('#graph');
	}
});
