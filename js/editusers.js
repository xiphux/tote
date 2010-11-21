function initDeleteConfirm()
{
	$('a.deleteLink').click(function() {
		var username = $(this).parent().parent().children('td.username').text();
		return confirm("Are you sure you want to delete this user?\r\n'" + username + "' will be removed from any pools he/she is in and permanently deleted.");
	});
}

$(document).ready(function() {
	initDeleteConfirm();
});
