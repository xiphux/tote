define(['cs!modules/pickriskgraph', 'cs!modules/analyticsunsupported', 'modernizr', 'common'], function(pickrisk, analyticsunsupported) {
	if (Modernizr.inlinesvg) {
		pickrisk('#graph', '#graphControls');
	} else {
		require(['jquery'], function() {
			$('div.navTabs').remove();
			analyticsunsupported('#graph');
		});
	}
});
