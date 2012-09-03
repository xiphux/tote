define(function() {
	if (!(location.hash || pageYOffset)) {
		window.scrollTo(0, 1);
	}
});
