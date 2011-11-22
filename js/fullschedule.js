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
				$(this).slideDown('fast');
			} else {
				$(this).slideUp('fast');
			}
		});
		$('.scheduleTocContent li').removeClass('activeTab');
		$(this).parent().addClass('activeTab');
	};

	var tocShowAllClick = function() {
		$('.divScheduleItem').slideDown('fast');
		$('.scheduleTocContent li').removeClass('activeTab');
		$(this).parent().addClass('activeTab');
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

function initScheduleTocPin() {
};

$(document).ready(function() {
	initSeasonNav();
	initScheduleToc();
	initScheduleTocPin();
});
