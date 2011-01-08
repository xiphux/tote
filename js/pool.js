function initPoolNav() {
	$('#poolNameSelect').change(function() {
		$(this).parent().submit();
	});
	$('#poolNameSubmit').remove();
};

$(document).ready(function() {
	initPoolNav();
});
