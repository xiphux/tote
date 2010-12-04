function initTeamLinks()
{
	$('span.teamName').each(function()
	{
		var teamName = $(this).text();
		var teamId = $(this).attr('id');

		if (!(teamName && teamId)) {
			return;
		}

		if ($('select#betSelect option[value="' + teamId + '"]').size() < 1) {
			return;
		}

		var link = jQuery(document.createElement('a'));
		link.attr('href', '#');
		link.text(teamName);
		link.click(function() {
			$('select#betSelect').val(teamId);
		});
		$(this).empty();
		$(this).append(link);
	});
};

$(document).ready(function() {
	initTeamLinks();
});
