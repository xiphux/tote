function initSeasonNav() {
	$('#seasonSelect').change(function() {
		$(this).parent().submit();
	});
	$('#seasonSubmit').remove();
};

$(document).ready(function() {
	initSeasonNav();
});
