function compareUsers(a, b) {
	var aname = a.find('span.username').text().toLowerCase();
	var bname = b.find('span.username').text().toLowerCase();

	if (aname < bname)
		return -1;
	else if (aname > bname)
		return 1;
	return 0;
}

function sortUsers(listID) {
	var users = [];
	$(listID + ' div.userListItem').each(function() {
		users.push($(this).detach());
	});
	users.sort(compareUsers);
	for (var i = 0; i < users.length; i++) {
		$(listID).append(users[i]);
	}
}

function initEditPool() {
	$('.availableUsers input:checkbox').live('click', function(e) {
		e.stopPropagation();
		if ($(this).is(':checked')) {
			$(this).parent().parent().parent().parent().parent().addClass('selected');
		} else {
			$(this).parent().parent().parent().parent().parent().removeClass('selected');
		}
		if ($('.availableUsers input:checkbox:checked').size() > 0) {
			$('#addButton').removeAttr('disabled');
		} else {
			$('#addButton').attr('disabled','disabled');
		}
	});
	$('.poolUsers input:checkbox').live('click', function(e) {
		e.stopPropagation();
		if ($(this).is(':checked')) {
			$(this).parent().parent().parent().parent().parent().addClass('selected');
		} else {
			$(this).parent().parent().parent().parent().parent().removeClass('selected');
		}
		if ($('.poolUsers input:checkbox:checked').size() > 0) {
			$('#removeButton').removeAttr('disabled');
		} else {
			$('#removeButton').attr('disabled','disabled');
		}
	});
	$('#addButton').click(function() {
		$(this).attr('disabled','disabled');
		$('input:checkbox').attr('disabled','disabled');
		$('#editSpinner').show();
		var aUsers = [];
		$('.availableUsers input:checkbox:checked').each(function() {
			aUsers.push($(this).parent().parent().parent().parent().parent().attr('id'));
		});
		$.ajax({
			url: 'index.php?a=ajaxeditpool',
			type: 'POST',
			data: {
				p: $('span#poolID').text(),
				m: 'add',
				u: aUsers,
				csrftoken: TOTE_CSRF_TOKEN
			},
			success: function(msg) {
				if (msg && (msg.length > 0)) {
					alert('An error occurred while updating the pool: ' + msg);
				}
				$('.availableUsers input:checkbox:checked').each(function() {
					$(this).removeAttr('checked');
					var aDiv = $(this).parent().parent().parent().parent().parent();
					aDiv.removeClass('selected');
					$('.poolUsers').append(aDiv.detach());
				});
				sortUsers('.poolUsers');
				$('input:checkbox').removeAttr('disabled');
				$('#editSpinner').hide();
			},
			error: function() {
				alert("An error occurred while updating the pool");
			}
		});
	});
	$('#removeButton').click(function() {
		$(this).attr('disabled','disabled');
		$('input:checkbox').attr('disabled','disabled');
		$('#editSpinner').show();
		var pUsers = [];
		$('.poolUsers input:checkbox:checked').each(function() {
			pUsers.push($(this).parent().parent().parent().parent().parent().attr('id'));
		});
		$.ajax({
			url: 'index.php?a=ajaxeditpool',
			type: 'POST',
			data: {
				p: $('span#poolID').text(),
				m: 'remove',
				u: pUsers,
				csrftoken: TOTE_CSRF_TOKEN
			},
			success: function(msg) {
				if (msg && (msg.length > 0)) {
					alert('An error occurred while updating the pool: ' + msg);
				}
				$('.poolUsers input:checkbox:checked').each(function() {
					$(this).removeAttr('checked');
					var pDiv = $(this).parent().parent().parent().parent().parent();
					pDiv.removeClass('selected');
					pDiv.find('td.alert').remove();
					$('.availableUsers').append(pDiv.detach());
				});
				sortUsers('.availableUsers');
				$('input:checkbox').removeAttr('disabled');
				$('#editSpinner').hide();
			},
			error: function() {
				alert("An error occurred while updating the pool");
			}
		});
	});
}

function initEditPoolTips()
{
	$('*[title]').qtip({
		style: {
			classes: 'ui-tooltip-light ui-tooltip-shadow'
		},
		position: {
			adjust: {
				screen: true
			}
		}
	});
}

$(document).ready(function() {
	initEditPool();
	initEditPoolTips();
});
