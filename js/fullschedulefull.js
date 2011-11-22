function initScheduleTocPin() {

	var toc = $('.scheduleTocContent');

	var tocYLoc = toc.position().top;
	var tocPosition = toc.css('position');
	var tocTop = toc.css('top');

	var pinned = false;

	$(window).scroll(function() {
		var windowYLoc = $(document).scrollTop();
		if (windowYLoc > tocYLoc) {
			if (!pinned) {
				toc.css('position', 'fixed');
				toc.css('top', '0px');
				pinned = true;
			}
		} else {
			if (pinned) {
				toc.css('position', tocPosition);
				toc.css('top', tocTop);
				pinned = false;
			}
		}
	});
};

$(document).ready(function() {
	initScheduleTocPin();
});
