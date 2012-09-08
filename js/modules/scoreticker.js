define(['jquery', 'module', 'modules/scoreticker/game', 'modules/scoreticker/gametile', 'modules/scoreticker/bigplay', 'cookies'], function($, module, Game, GameTile, BigPlay) {

	var Tote = {
	};

	Tote.ScoreTicker = {
	};

	Tote.ScoreTicker.BigPlayPopup = function()
	{
		this._data = {
			team: null,
			message: null,
			animationSpeed: 400,
			shown: false,
			reverse: false
		};
		this._elements = {
			tile: null,
			content: null,
			team: null,
			message: null
		};

		this._width = 150;
	};

	Tote.ScoreTicker.BigPlayPopup.CSSClasses = {
		bigPlay: 'tickerBigPlay',
		bigPlayContent: 'tickerBigPlayContent',
		bigPlayRight: 'tickerBigPlayRight',
		bigPlayLeft: 'tickerBigPlayLeft',
		bigPlayTeam: 'tickerBigPlayTeam',
		bigPlayMessage: 'tickerBigPlayMessage'
	};

	Tote.ScoreTicker.BigPlayPopup.prototype = {

		initialize: function()
		{
			this._buildElement();
		},

		_buildElement: function()
		{
			var tile = jQuery(document.createElement('div'));
			tile.addClass(Tote.ScoreTicker.BigPlayPopup.CSSClasses.bigPlay);
			tile.width(0);
			tile.css('opacity', 0.25);

			var content = jQuery(document.createElement('div'));
			content.addClass(Tote.ScoreTicker.BigPlayPopup.CSSClasses.bigPlayContent);

			var team = jQuery(document.createElement('div'));
			team.addClass(Tote.ScoreTicker.BigPlayPopup.CSSClasses.bigPlayTeam);
			content.append(team);

			var message = jQuery(document.createElement('div'));
			message.addClass(Tote.ScoreTicker.BigPlayPopup.CSSClasses.bigPlayMessage);
			content.append(message);

			tile.append(content);

			this._elements.tile = tile;
			this._elements.content = content;
			this._elements.team = team;
			this._elements.message = message;
		},

		show: function(afterShow)
		{
			if (this._data.shown) {
				return;
			}

			if (this._data.reverse) {
				this._elements.content.addClass(Tote.ScoreTicker.BigPlayPopup.CSSClasses.bigPlayLeft);
				this._elements.content.removeClass(Tote.ScoreTicker.BigPlayPopup.CSSClasses.bigPlayRight);
			} else {
				this._elements.content.addClass(Tote.ScoreTicker.BigPlayPopup.CSSClasses.bigPlayRight);
				this._elements.content.removeClass(Tote.ScoreTicker.BigPlayPopup.CSSClasses.bigPlayLeft);
			}

			var anim = {
				width: this._width + 'px',
				opacity: 1
			};

			if (this._data.reverse) {
				anim.left = (this.get_left() - this._width) + 'px';
			}

			this._elements.tile.animate(anim, this._data.animationSpeed, 'swing', function() {
				if (afterShow) {
					afterShow();
				}
			});

			this._data.shown = true;
		},

		hide: function(afterHide)
		{
			if (!this._data.shown) {
				return;
			}

			var anim = {
				width: '0px',
				opacity: 0.25
			};

			if (this._data.reverse) {
				anim.left = (this.get_left() + this._width) + 'px';
			}

			this._elements.tile.animate(anim, this._data.animationSpeed, 'swing', function() {
				if (afterHide) {
					afterHide();
				}
			});

			this._data.shown = false;
		},

		is_showing: function()
		{
			return this._data.shown;
		},

		get_team: function()
		{
			return this._data.team;
		},

		set_team: function(team)
		{
			this._data.team = team;
			this._elements.team.text(team);
		},

		get_message: function()
		{
			return this._data.message;
		},

		set_message: function(message)
		{
			this._data.message = message;
			this._elements.message.text((message.length > 52) ? (message.substring(0, 52) + "...") : message);
		},

		get_reverse: function()
		{
			return this._data.reverse;
		},

		set_reverse: function(reverse)
		{
			if (this._data.shown) {
				return;
			}
			this._data.reverse = reverse;
		},

		get_height: function()
		{
			return this._elements.tile.height();
		},

		set_height: function(height)
		{
			this._elements.tile.height(height);
		},

		get_left: function()
		{
			return this._elements.tile.position().left;
		},

		set_left: function(left)
		{
			this._elements.tile.css('left', left + "px");
		},

		get_top: function()
		{
			return this._elements.tile.position().top;
		},

		set_top: function(t)
		{
			this._elements.tile.css('top', t + "px");
		},

		get_animationSpeed: function()
		{
			return this._data.animationSpeed;
		},

		set_animationSpeed: function(animationSpeed)
		{
			this._data.animationSpeed = animationSpeed;
		},

		set_data: function(data)
		{
			for (var prop in data) {
				if (data.hasOwnProperty(prop)) {
					switch (prop) {
						case 'team':
							this.set_team(data.team);
							break;
						case 'message':
							this.set_message(data.message);
							break;
						case 'reverse':
							this.set_reverse(data.reverse);
							break;
						case 'height':
							this.set_height(data.height);
							break;
						case 'left':
							this.set_left(data.left);
							break;
						case 'top':
							this.set_top(data.top);
							break;
						case 'animationSpeed':
							this.set_animationSpeed(data.animationSpeed);
							break;
					}
				}
			}
		},

		get_element: function()
		{
			return this._elements.tile;
		}

	};

	Tote.ScoreTicker.Ticker = function()
	{
		this._elements = {
			bound: null,
			containerDiv: null,
			toggleDiv: null,
			toggleLink: null,
			titleDiv: null,
			gameTable: null,
			gameRow: null
		};

		this._gameObjects = {};

		this._bigPlayPopup = null;
		this._showBigPlays = true;

		this._displayedBigPlays = [];
		this._bigPlayQueue = [];
	};

	Tote.ScoreTicker.Ticker.CSSClasses = {
		toggleLink: 'tickerToggleLink',
		toggleDiv: 'tickerToggleDiv',

		titleDiv: 'tickerTitle',
		containerDiv: 'tickerContainerDiv',

		closed: 'tickerClosed',
		open: 'tickerOpen',

		gameTable: 'tickerGameTable',
		gameCell: 'tickerGameCell'
	};

	Tote.ScoreTicker.Ticker.Labels = {
		showLink: 'Score ticker...',
		hideLink: 'Score ticker'
	};

	Tote.ScoreTicker.Ticker.URLs = {
		ticker: 'scoreticker.php'
	};

	Tote.ScoreTicker.Ticker.prototype = {

		_started: false,
		_delay: 15,
		_timerId: null,

		initialize: function(element, hidden)
		{
			if (!element || (element.size() <= 0)) {
				return;
			}
			this._elements.bound = element;
			//$.get(Tote.ScoreTicker.Ticker.URLs.ticker, {}, $.proxy(function (xml) {
			//	var gms = $(xml).find('gms');
			//	if (gms.attr('t') == 'P') {
			//		return;		// don't show preseason ticker
			//	}
			//	this._initUI(hidden);
			//	this._update();
			//}, this), 'xml');
			this._bigPlayPopup = new Tote.ScoreTicker.BigPlayPopup();
			this._bigPlayPopup.initialize();
			this._initUI(hidden);
			if (!hidden) {
				this.start();
			}
		},

		_initUI: function(hidden)
		{
			var toggleDiv = jQuery(document.createElement('div'));
			toggleDiv.addClass(Tote.ScoreTicker.Ticker.CSSClasses.toggleDiv);
			var toggleLink = jQuery(document.createElement('a'));
			toggleLink.addClass(Tote.ScoreTicker.Ticker.CSSClasses.toggleLink);
			if (hidden) {
				toggleLink.text(Tote.ScoreTicker.Ticker.Labels.showLink);
				toggleLink.addClass(Tote.ScoreTicker.Ticker.CSSClasses.closed);
			} else {
				toggleLink.text(Tote.ScoreTicker.Ticker.Labels.hideLink);
				toggleLink.addClass(Tote.ScoreTicker.Ticker.CSSClasses.open);
			}
			toggleLink.attr('href', '#');
			var ticker = this;
			var showCallback = function(event) {
				ticker._showBigPlays = false;
				ticker.start();
				if ($.cookies.test()) {
					var exp = new Date();
					exp.setDate(exp.getDate() + 365);
					$.cookies.set('ToteScoretickerHidden', false, {expiresAt: exp});
				}
				ticker._elements.bound.removeClass('rounded-bottom');
				ticker._elements.containerDiv.show('fast', function() {
					ticker._elements.gameTable.animate({marginLeft: 0}, 'fast', function() {
						ticker._showBigPlays = true;
					});
					ticker._elements.toggleLink.text(Tote.ScoreTicker.Ticker.Labels.hideLink);
					ticker._elements.toggleLink.removeClass(Tote.ScoreTicker.Ticker.CSSClasses.closed);
					ticker._elements.toggleLink.addClass(Tote.ScoreTicker.Ticker.CSSClasses.open);
				});
				return false;
			};
			var hideCallback = function(event) {
				ticker.stop();
				if ($.cookies.test()) {
					var exp = new Date();
					exp.setDate(exp.getDate() + 365);
					$.cookies.set('ToteScoretickerHidden', true, {expiresAt: exp});
				}
				var animateHide = function() {
					ticker._elements.gameTable.animate({marginLeft: -ticker._elements.gameTable.outerWidth()}, 'fast', function() {
						ticker._elements.containerDiv.hide('fast');
						ticker._elements.bound.addClass('rounded-bottom');
						ticker._elements.toggleLink.text(Tote.ScoreTicker.Ticker.Labels.showLink);
						ticker._elements.toggleLink.removeClass(Tote.ScoreTicker.Ticker.CSSClasses.open);
						ticker._elements.toggleLink.addClass(Tote.ScoreTicker.Ticker.CSSClasses.closed);
					});
				};
				if (ticker._bigPlayPopup.is_showing()) {
					ticker._bigPlayPopup.hide(animateHide);
				} else {
					animateHide();
				}
				return false;
			};
			if (hidden) {
				toggleLink.toggle(showCallback, hideCallback);
			} else {
				toggleLink.toggle(hideCallback, showCallback);
			}
			toggleDiv.append(toggleLink);
			this._elements.bound.append(toggleDiv);
			this._elements.toggleLink = toggleLink;
			this._elements.toggleDiv = toggleDiv;

			var containerDiv = jQuery(document.createElement('div'));
			containerDiv.addClass(Tote.ScoreTicker.Ticker.CSSClasses.containerDiv);
			containerDiv.addClass('subSection');
			if (hidden) {
				containerDiv.hide();
			}

			var titleDiv = jQuery(document.createElement('div'));
			titleDiv.addClass(Tote.ScoreTicker.Ticker.CSSClasses.titleDiv);
			containerDiv.append(titleDiv);
			this._elements.titleDiv = titleDiv;
			
			var gameTable = jQuery(document.createElement('table'));
			gameTable.addClass(Tote.ScoreTicker.Ticker.CSSClasses.gameTable);

			var row = jQuery(document.createElement('tr'));
			gameTable.append(row);

			containerDiv.append(gameTable);

			containerDiv.append(this._bigPlayPopup.get_element());

			this._elements.gameTable = gameTable;
			this._elements.gameRow = row;

			this._elements.bound.append(containerDiv);
			this._elements.containerDiv = containerDiv;

			if (hidden) {
				this._elements.bound.width(900);
				gameTable.css('margin-left', -this._elements.bound.outerWidth());
				this._elements.bound.addClass('rounded-bottom');
			}
		},

		_update: function()
		{
			$.get(Tote.ScoreTicker.Ticker.URLs.ticker, {}, $.proxy(function (xml) { this._updateSuccess(xml); }, this), 'xml');
		},

		_updateSuccess: function(xml)
		{
			if (!this._started) {
				return;
			}

			var gms = $(xml).find('gms');
			
			this._updateTitle(gms.attr('y'), gms.attr('w'), gms.attr('t'));

			this._updateGameTiles(gms);

			var bps = $(xml).find('bps');

			this._updateBigPlays(bps);
			if (this._showBigPlays && !this._bigPlayPopup.is_showing()) {
				this._showNextBigPlay();
			}

			this._elements.bound.width(this._elements.gameTable.width() + 4);

			if ((gms.attr('gd') === '1') && (this._hasActiveGames())) {
				this.set_delay(15);
			} else {
				this.set_delay(300);
			}

			if (this._started) {
				this._timerId = window.setTimeout($.proxy(function() { this._update(); }, this), this._delay * 1000);
			}
		},

		_updateTitle: function(year, week, type)
		{
			var title = year + '-' + (year * 1 + 1) + ' ' + (type === 'P' ? 'preseason ' : '') + 'week ' + week;
			this._elements.titleDiv.text(title);
		},

		_updateGameTiles: function(gameslist)
		{
			var instance = this;
			var updated = [];

			gameslist.find('g').each(function() {

				var g = $(this);

				var gsis = g.attr('gsis');
				var gameObj = null;

				if (instance._gameObjects[gsis]) {
					gameObj = instance._gameObjects[gsis];
					gameObj.get_game().set_data({
						eid: g.attr('eid'),
						start: {
							day: g.attr('d'),
							time: g.attr('t')
						},
						visitor: g.attr('v'),
						visitorNickname: g.attr('vnn'),
						home: g.attr('h'),
						homeNickname: g.attr('hnn'),
						quarter: g.attr('q'),
						clock: g.attr('k'),
						redZone: (g.attr('rz') === '1' ? true : false),
						possession: g.attr('p'),
						homeScore: g.attr('hs'),
						visitorScore: g.attr('vs')
					});
				} else {
					var gameData = new Game();
					gameData.set_data({
						year: gameslist.attr('y'),
						seasonType: gameslist.attr('t'),
						week: gameslist.attr('w'),
						gsis: gsis,
						eid: g.attr('eid'),
						start: {
							day: g.attr('d'),
							time: g.attr('t')
						},
						visitor: g.attr('v'),
						visitorNickname: g.attr('vnn'),
						home: g.attr('h'),
						homeNickname: g.attr('hnn'),
						quarter: g.attr('q'),
						clock: g.attr('k'),
						redZone: (g.attr('rz') === '1' ? true : false),
						possession: g.attr('p'),
						homeScore: g.attr('hs'),
						visitorScore: g.attr('vs')
					});

					gameObj = new GameTile(gameData);

					gameObj.initialize();
					instance._gameObjects[gsis] = gameObj;

					var td = jQuery(document.createElement('td'));
					td.addClass(Tote.ScoreTicker.Ticker.CSSClasses.gameCell);
					td.append(gameObj.get_element());
					instance._elements.gameRow.append(td);
				}

				updated.push(gsis);
			});

			this._elements.gameRow.find('td.' + Tote.ScoreTicker.Ticker.CSSClasses.gameCell + ' > a').each(function() {
				if (jQuery.inArray($(this).attr('id'), updated) === -1) {
					$(this).parent().remove();
				}
			});
		},

		_updateBigPlays: function(bigPlayList)
		{
			if (!bigPlayList) {
				return;
			}

			var bps = bigPlayList.find('b');
			if (bps.size() < 1) {
				return;
			}

			var ticker = this;

			bps.each(function() {
				var b = $(this);

				var id = b.attr('id');
				if (!id) {
					return;
				}

				if (ticker._displayedBigPlays[id]) {
					return;
				}

				ticker._displayedBigPlays[id] = true;

				var bpObj = new BigPlay();
				bpObj.set_data({
					id: id,
					gsis: b.attr('gsis'),
					eid: b.attr('eid'),
					team: b.attr('abbr'),
					message: b.attr('x')
				});

				ticker._bigPlayQueue.push(bpObj);
			});
		},

		_showNextBigPlay: function()
		{
			if (this._bigPlayQueue.length < 1) {
				return;
			}

			var bpObj = this._bigPlayQueue[0];
			if (!bpObj) {
				return;
			}

			var count = 0;
			var gsis = null;
			for (gsis in this._gameObjects) {
				if (this._gameObjects.hasOwnProperty(gsis)) {
					count++;
				}
			}
			var half = Math.ceil(count/2);
			var idx = 0;

			var ticker = this;
			gsis = null;
			for (gsis in this._gameObjects) {
				if (this._gameObjects.hasOwnProperty(gsis)) {
					if (this._gameObjects[gsis]) {
						idx++;
						if (gsis === bpObj.get_gsis()) {
							var gameElem = this._gameObjects[gsis].get_element();
							var pos = gameElem.position();
							var bpPos = pos.left;
							this._bigPlayPopup.set_data({
								team: bpObj.get_team(),
								message: bpObj.get_message(),
								reverse: (idx >= half),
								height: this._elements.gameTable.height(),
								top: pos.top,
								left: (idx >= half) ? pos.left : (pos.left + gameElem.width())
							});
							this._bigPlayPopup.show(function() {
								window.setTimeout($.proxy(function() { this._bigPlayFinished(); }, ticker), 10000);
							});
							return;
						}
					}
				}
			}
		},

		_bigPlayFinished: function()
		{
			var ticker = this;
			this._bigPlayPopup.hide(function() {
				if (ticker._bigPlayQueue.length > 0) {
					ticker._bigPlayQueue.shift();
				}
				if ((ticker._bigPlayQueue.length > 0) && ticker._started) {
					ticker._showNextBigPlay();
				}
			});
		},

		_hasActiveGames: function()
		{
			for (var gsis in this._gameObjects) {
				if (this._gameObjects.hasOwnProperty(gsis)) {
					if (this._gameObjects[gsis] && (this._gameObjects[gsis].get_game().active())) {
						return true;
					}
				}
			}
			return false;
		},

		get_delay: function() {
			return this._delay;
		},

		set_delay: function(value) {
			if (this._delay === value) {
				return;
			}

			this._delay = value;
			if (this._started) {
				this.stop();
				this.start();
			}
		},

		start: function() {
			if (this._started) {
				return;
			}

			this._started = true;
			this._update();
		},

		stop: function() {
			this._started = false;
			if (this._timerId) {
				window.clearTimeout(this._timerId);
				this._timerId = null;
			}
			this._bigPlayQueue = [];
		},

		get_started: function() {
			return this._started;
		}

	};

	var hidden = false;
	if ($.cookies.test()) {
		var ck = $.cookies.get('ToteScoretickerHidden');
		if (ck !== null) {
			hidden = ck;
		}
	}
	var ticker = new Tote.ScoreTicker.Ticker();
	ticker.initialize($('#scoreTicker'), hidden);

});
