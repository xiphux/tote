define(['jquery'], function() {
	return function(select, submit) {
		if (select) {
			$(select).change(function() {
				$(this).closest('form').submit();
			});
		}
		if (submit) {
			$(submit).remove();
		}
	}
})
