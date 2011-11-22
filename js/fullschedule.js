function initSeasonNav() {
	$('#seasonSelect').change(function() {
		$(this).parent().submit();
	});
	$('#seasonSubmit').remove();
};

function initScheduleToc() {
	var tocContent = $('.scheduleTocContent');

	var tocItemClick = function() {
		var itemId = $(this).attr('href').substr(1);
		$('.divScheduleItem').each(function() {
			if ($(this).attr('id') == itemId) {
				if (mobile) {
					$(this).show();
				} else {
					$(this).slideDown('fast');
				}
			} else {
				if (mobile) {
					$(this).hide();
				} else {
					$(this).slideUp('fast');
				}
			}
		});
		$('.scheduleTocContent li').removeClass('activeTab');
		$(this).parent().addClass('activeTab');
		return mobile;
	};

	var tocShowAllClick = function() {
		if (mobile) {
			$('.divScheduleItem').show();
		} else {
			$('.divScheduleItem').slideDown('fast');
		}
		$('.scheduleTocContent li').removeClass('activeTab');
		$(this).parent().addClass('activeTab');
		return false;
	};

	tocContent.find('a').click(tocItemClick);

	var showAllLink = jQuery(document.createElement('a'));
	showAllLink.attr('href', '#');
	showAllLink.text('(Show All)');
	showAllLink.click(tocShowAllClick);

	var li = jQuery(document.createElement('li'));
	li.addClass('activeTab');
	li.append(showAllLink);
	tocContent.find('ul').prepend(li);
};

$(document).ready(function() {
	initSeasonNav();
	initScheduleToc();
});
