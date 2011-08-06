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
			return false;
		});
		$(this).empty();
		$(this).append(link);
	});
};

function initBetConfirm()
{
	$('#frmBet').submit(function() {
		if (!$('select[name="t"]').val()) {
			return false;
		}
		var week = $('input[name="w"]').val();
		var team = $('select[name="t"] option:selected').text();
		return confirm("Are you sure you want to pick the " + team + " for week " + week + "?\nOnce this pick is made it cannot be changed.");
	});
};

$(document).ready(function() {
	initTeamLinks();
	initBetConfirm();
});
