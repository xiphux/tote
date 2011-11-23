function getInternetExplorerVersion() {
	var rv = -1;
	if (navigator.appName == 'Microsoft Internet Explorer') {
		var ua = navigator.userAgent;
		var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null)
			rv = parseFloat(RegExp.$1);
	}
	return rv;
}

function canAnim() {
	var ver = getInternetExplorerVersion();
	return (ver === -1) || (ver > 8);
}

function initSeasonNav() {
	$('#seasonSelect').change(function() {
		$(this).parent().submit();
	});
	$('#seasonSubmit').remove();
};

function initScheduleToc() {
	var tocContent = $('.scheduleTocContent');

	var anim = canAnim();

	var tocItemClick = function() {
		if (!mobile) {
			window.scrollTo(0, 1);
		}
		var itemId = $(this).attr('href').substr(1);
		$('.divScheduleItem').each(function() {
			if ($(this).attr('id') == itemId) {
				if (mobile || !anim) {
					$(this).show();
				} else {
					$(this).slideDown('fast');
				}
			} else {
				if (mobile || !anim) {
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
		if (!mobile) {
			window.scrollTo(0, 1);
		}
		if (mobile || !anim) {
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
