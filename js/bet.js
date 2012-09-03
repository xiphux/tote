define(['jquery', 'qtip', 'common'], function($) {

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
	
	$('#frmBet').submit(function() {
		if (!$('select[name="t"]').val()) {
			return false;
		}
		var week = $('input[name="w"]').val();
		var team = $('select[name="t"] option:selected').text();
		return confirm("Are you sure you want to pick the " + team + " for week " + week + "?\nOnce this pick is made it cannot be changed.");
	});

	var url = window.location.href.match(/^([^\?]+\/)/);
        if (!url) {
                return;
        }
	url = url[1];

	var season = $('#poolSeason').text();
	if (!season) {
		return;
	}

	var week = $('input[name="w"]').val();

	$('span.teamName').each(function()
	{
		var teamid = $(this).attr('id');
		if (!teamid) {
			return;
		}

		var teamName = $(this).attr('title');

		$(this).qtip(
		{
			content: {
				text: '<img src="' + url + 'images/editpool-loader.gif" alt="Loading..." />',
				ajax: {
					url: 'index.php',
					data: {
						a: 'teamschedule',
						t: teamid,
						o: 'js',
						w: week
					},
					type: 'GET'
				},
				title: {
					text: teamName + ' Team Schedule'
				}
			},
			style: {
				classes: 'ui-tooltip-tote ui-tooltip-shadow ui-tooltip-rounded'
			},
			position: {
				adjust: {
					screen: true
				},
				my: 'left center',
				at: 'right center'
			}
		});
	});

});
