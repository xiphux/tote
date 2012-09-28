define(['jquery', 'module', 'cs!modules/autoselectnav', 'cs!modules/cananimate', 'common'], function($, module, autoselectnav, cananimate) {
	autoselectnav('#seasonSelect', '#seasonSubmit');

	var mobile = module.config().mobile;

	var tocContent = $('.scheduleTocContent');

	var tocItemClick = function() {
		if (!mobile) {
			window.scrollTo(0, 1);
		}
		var itemId = $(this).attr('href').substr(1);
		$('.divScheduleItem').each(function() {
			if ($(this).attr('id') == itemId) {
				if (mobile || !cananimate) {
					$(this).show();
				} else {
					$(this).slideDown('fast');
				}
			} else {
				if (mobile || !cananimate) {
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
		if (mobile || !cananimate) {
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

	if (!mobile) {
		var tocYLoc = tocContent.position().top;
		var tocPosition = tocContent.css('position');
		var tocTop = tocContent.css('top');
		var tocParentWidth = tocContent.parent().width()+1;

		var pinned = false;

		$(window).scroll(function() {
			var windowYLoc = $(document).scrollTop();
			if (windowYLoc > tocYLoc) {
				if (!pinned) {
					tocContent.css('position', 'fixed');
					tocContent.css('top', '0px');
					tocContent.parent().width(tocContent.width()+1);
					pinned = true;
				}
			} else {
				if (pinned) {
					tocContent.css('position', tocPosition);
					tocContent.css('top', tocTop);
					tocContent.parent().width(tocParentWidth);
					pinned = false;
				}
			}
		});
	}

});
