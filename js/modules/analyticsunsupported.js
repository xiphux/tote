define(['jquery'], function() {
	return function(messageContainer) {
		var container = $(messageContainer);

		var line1 = $(document.createElement('div'))
			.text('Analytics requires features that your browser does not support.');
		container.append(line1);

		var line2 = $(document.createElement('div'))
			.css('padding-bottom', '10px')
			.text('Please upgrade to one of the following browsers:');
		container.append(line2);

		var chrome = $(document.createElement('div'));
		var chromelink = $(document.createElement('a'))
			.attr('href', 'http://www.google.com/chrome')
			.attr('target', '_blank')
			.text('Google Chrome');
		chrome.append(chromelink);
		container.append(chrome);

		var firefox = $(document.createElement('div'));
		var firefoxlink = $(document.createElement('a'))
			.attr('href', 'http://www.getfirefox.com')
			.attr('target', '_blank')
			.text('Mozilla Firefox');
		firefox.append(firefoxlink);
		container.append(firefox);

		var ie9 = $(document.createElement('div'));
		var ie9link = $(document.createElement('a'))
			.attr('href', 'http://windows.microsoft.com/en-us/internet-explorer/products/ie/home')
			.attr('target', '_blank')
			.text('Internet Explorer 9');
		ie9.append(ie9link);
		container.append(ie9);
	}
});
