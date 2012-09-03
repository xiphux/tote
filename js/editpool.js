define(['jquery', 'module', 'modules/titletips', 'common'], function($, module) {

	$('a.deletePoolAction').click(function() {
		var poolname = $('#poolName').val();
		return confirm("Are you sure you want to delete the pool \"" + poolname + "\"?\r\nThis pool and all its bets will be permanently removed.");
	});

	var compareUsers = function(a, b) {
		var aname = a.find('span.username').text().toLowerCase();
		var bname = b.find('span.username').text().toLowerCase();

		if (aname < bname)
			return -1;
		else if (aname > bname)
			return 1;
		return 0;
	}

	var sortUsers = function(listID) {
		var users = [];
		$(listID + ' div.userListItem').each(function() {
			users.push($(this).detach());
		});
		users.sort(compareUsers);
		for (var i = 0; i < users.length; i++) {
			$(listID).append(users[i]);
		}
	}

	$('.availableUsers .selectuser').live('click', function(e) {
		e.stopPropagation();
		if ($(this).is(':checked')) {
			$(this).parent().parent().parent().parent().parent().addClass('selected');
		} else {
			$(this).parent().parent().parent().parent().parent().removeClass('selected');
		}
		if ($('.availableUsers .selectuser:checked').size() > 0) {
			$('#addButton').removeAttr('disabled');
		} else {
			$('#addButton').attr('disabled','disabled');
		}
	});
	$('.poolUsers .selectuser').live('click', function(e) {
		e.stopPropagation();
		if ($(this).is(':checked')) {
			$(this).parent().parent().parent().parent().parent().addClass('selected');
		} else {
			$(this).parent().parent().parent().parent().parent().removeClass('selected');
		}
		if ($('.poolUsers .selectuser:checked').size() > 0) {
			$('#removeButton').removeAttr('disabled');
		} else {
			$('#removeButton').attr('disabled','disabled');
		}
	});
	$('#addButton').click(function() {
		$(this).attr('disabled','disabled');
		$('.selectuser').attr('disabled','disabled');
		$('#editSpinner').show();
		var aUsers = [];
		$('.availableUsers .selectuser:checked').each(function() {
			aUsers.push($(this).parent().parent().parent().parent().parent().attr('id'));
		});
		$.ajax({
			url: 'index.php?a=ajaxeditpool',
			type: 'POST',
			data: {
				p: $('span#poolID').text(),
				m: 'add',
				u: aUsers,
				csrftoken: module.config().csrftoken
			},
			success: function(msg) {
				if (msg && (msg.length > 0)) {
					alert('An error occurred while updating the pool: ' + msg);
				}
				$('.availableUsers .selectuser:checked').each(function() {
					$(this).removeAttr('checked');
					var aDiv = $(this).parent().parent().parent().parent().parent();
					aDiv.removeClass('selected');
					aDiv.find('.poolAdmin').show();
					$('.poolUsers').append(aDiv.detach());
				});
				sortUsers('.poolUsers');
				$('.selectuser').removeAttr('disabled');
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
		$('.poolUsers .selectuser:checked').each(function() {
			pUsers.push($(this).parent().parent().parent().parent().parent().attr('id'));
		});
		$.ajax({
			url: 'index.php?a=ajaxeditpool',
			type: 'POST',
			data: {
				p: $('span#poolID').text(),
				m: 'remove',
				u: pUsers,
				csrftoken: module.config().csrftoken
			},
			success: function(msg) {
				if (msg && (msg.length > 0)) {
					alert('An error occurred while updating the pool: ' + msg);
				}
				$('.poolUsers .selectuser:checked').each(function() {
					$(this).removeAttr('checked');
					var pDiv = $(this).parent().parent().parent().parent().parent();
					pDiv.removeClass('selected');
					pDiv.find('td.alert').remove();
					pDiv.find('.poolAdmin').hide();
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
	$('.poolUsers .admincheckbox').live('click', function(e) {
		e.stopPropagation();
		var div = $(this).parent().parent().parent().parent().parent();
		var primaryadmin = div.find('.primaryadmin');
		var secondaryadmin = div.find('.secondaryadmin');
		var primarychecked = primaryadmin.is(':checked');
		var secondarychecked = secondaryadmin.is(':checked');
		if ($(this)[0] == primaryadmin[0]) {
			if (primarychecked) {
				secondaryadmin.removeAttr('checked');
				secondarychecked = false;
			}
		} else if ($(this)[0] == secondaryadmin[0]) {
			if (secondarychecked) {
				primaryadmin.removeAttr('checked');
				primarychecked = false;
			}
		}
		primaryadmin.attr('disabled','disabled');
		secondaryadmin.attr('disabled','disabled');
		$.ajax({
			url: 'index.php?a=setpooladmin',
			type: 'POST',
			data: {
				p: $('span#poolID').text(),
				u: div.attr('id'),
				type: secondarychecked ? 2 : (primarychecked ? 1 : 0),
				csrftoken: module.config().csrftoken
			},
			success: function(msg) {
				if (msg && (msg.length > 0)) {
					alert('An error occurred while updating the pool: ' + msg);
				}
				secondaryadmin.removeAttr('disabled');
				primaryadmin.removeAttr('disabled');
			},
			error: function() {
				alert("An error occurred while updating the pool");
			}
		});
	});

});
