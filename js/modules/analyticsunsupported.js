define(['d3'], function() {
	return function(messageContainer) {
		var msg = d3.select(messageContainer);
		msg.append('div')
			.text('Analytics requires features that your browser does not support.');
		msg.append('div')
			.style('padding-bottom', '10px')
			.text('Please upgrade to one of the following browsers:');
		msg.append('div')
			.append('a')
			.attr('href', 'http://www.google.com/chrome')
			.attr('target', '_blank')
			.text('Google Chrome');
		msg.append('div')
			.append('a')
			.attr('href', 'http://www.getfirefox.com')
			.attr('target', '_blank')
			.text('Mozilla Firefox');
		msg.append('div')
			.append('a')
			.attr('href', 'http://windows.microsoft.com/en-us/internet-explorer/products/ie/home')
			.attr('target', '_blank')
			.text('Internet Explorer 9');
	}
});
