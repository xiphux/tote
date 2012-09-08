define(['jquery'], function ($) {
	
	function BigPlayPopup(bigPlay, gameTile, reverse) {
		this.__bigPlay = bigPlay;
		this.__gameTile = gameTile;
		this.__reverse = reverse;
	}

	BigPlayPopup.prototype = {

		__bigPlay: null,
		__gameTile: null,

		__reverse: false,

		__popup: null,
		__content: null,
		__team: null,
		__message: null,

		__visible: false,

		initialize: function()
		{
			this.__initElement();
			this.__initState();
		},

		__initElement: function()
		{
			var popup = $(document.createElement('div'));
			popup.addClass('tickerBigPlay');
			popup.width(0);
			this.__popup = popup;

			var content = $(document.createElement('div'));
			content.addClass('tickerBigPlayContent');
			if (this.__reverse) {
				content.addClass('tickerBigPlayLeft');
			} else {
				content.addClass('tickerBigPlayRight');
			}
			this.__content = content;

			var team = $(document.createElement('div'));
			team.addClass('tickerBigPlayTeam');
			this.__team = team;
			content.append(team);

			var message = $(document.createElement('div'));
			message.addClass('tickerBigPlayMessage');
			this.__message = message;
			content.append(message);

			popup.append(content);
		},

		__initState: function()
		{
			this.__team.text(this.__bigPlay.get_team());
			var message = this.__bigPlay.get_message();
			if (message.length > 52) {
				message = message.substr(0, 52) + '...';
			}
			this.__message.text(message);
		},

		show: function(callback)
		{
			if (this.__visible)
				return;

			var pos = this.__gameTile.get_position();
			var top = pos.top;
			if (!this.__reverse) {
				top += 1;
			}
			this.__popup.css('top', top + 'px');
			
			var left = pos.left + 1;
			if (!this.__reverse) {
				left += this.__gameTile.get_width() + 1;
			}
			this.__popup.css('left', left + 'px');

			var height = this.__gameTile.get_height();
			this.__popup.height(height);

			var anim = {
				width: '150px'
			};
			if (this.__reverse) {
				anim.left = (left - 150) + 'px';
			}

			this.__popup.animate(anim, 400, 'swing', function() {
				if (callback) {
					callback();
				}
			});

			this.__visible = true;
		},

		hide: function(callback)
		{
			if (!this.__visible)
				return;

			var anim = {
				width: '0px'
			};
			if (this.__reverse) {
				var pos = this.__gameTile.get_position();
				anim.left = pos.left + 'px';
			}

			this.__popup.animate(anim, 400, 'swing', function() {
				if (callback) {
					callback();
				}
			});

			this.__visible = false;
		},

		get_bigPlay: function()
		{
			return this.__bigPlay;
		},

		get_gameTile: function()
		{
			return this.__gameTile;
		},

		visible: function()
		{
			return this.__visible;
		},

		get_element: function()
		{
			return this.__popup;
		},

		observeChange: function(object, changeType, changeData)
		{
			if (object !== this.__bigPlay)
				return;

			if (changeType !== 'propertychanged')
				return;

			if (!changeData)
				return;

			for (var key in changeData) {
				if (!changeData.hasOwnProperty(key))
					continue;

				switch (key) {
					case 'team':
						this.__team.text(changeData.team);
						break;
					case 'message':
						var message = changeData.message;
						if (message.length > 52) {
							message = message.substr(0, 52) + '...';
						}
						this.__message.text(message);
						break;
				}
			}
		}

	};

	return BigPlayPopup;

});
