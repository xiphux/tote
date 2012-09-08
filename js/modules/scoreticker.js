define(['jquery', 'module', './scoreticker/game', './scoreticker/gametile', './scoreticker/bigplay', './scoreticker/bigplaypopup', './scoreticker/bigplayqueue', 'cookies'], function($, module, Game, GameTile, BigPlay, BigPlayPopup, BigPlayQueue) {

	var Tote = {
	};

	Tote.ScoreTicker = {
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
				ticker.start();
				if ($.cookies.test()) {
					var exp = new Date();
					exp.setDate(exp.getDate() + 365);
					$.cookies.set('ToteScoretickerHidden', false, {expiresAt: exp});
				}
				ticker._elements.bound.removeClass('rounded-bottom');
				ticker._elements.containerDiv.show('fast', function() {
					ticker._elements.gameTable.animate({marginLeft: 0}, 'fast');
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
				animateHide();
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

			this._elements.gameTable = gameTable;
			this._elements.gameRow = row;

			this._elements.bound.append(containerDiv);
			this._elements.containerDiv = containerDiv;

			if (hidden) {
				this._elements.bound.width(900);
				gameTable.css('margin-left', -this._elements.bound.outerWidth());
				this._elements.bound.addClass('rounded-bottom');
			}

			this._bigPlayQueue = new BigPlayQueue(containerDiv);
			if (!hidden) {
				this._bigPlayQueue.start();
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
			var count = 0;
			var gsis = null;
			for (gsis in this._gameObjects) {
				if (this._gameObjects.hasOwnProperty(gsis)) {
					count++;
				}
			}
			var half = Math.ceil(count/2);

			bps.each(function() {
				var b = $(this);

				var bpObj = new BigPlay();
				bpObj.set_data({
					id: b.attr('id'),
					gsis: b.attr('gsis'),
					eid: b.attr('eid'),
					team: b.attr('abbr'),
					message: b.attr('x')
				});

				var idx = 0;
				for (var gsis in ticker._gameObjects) {
					if (ticker._gameObjects.hasOwnProperty(gsis)) {
						if (ticker._gameObjects[gsis]) {
							idx++;
							if (gsis === bpObj.get_gsis()) {
								var popup = new BigPlayPopup(bpObj, ticker._gameObjects[gsis], (idx >= half));
								ticker._bigPlayQueue.push(popup);
								return;
							}
						}
					}
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
			this._bigPlayQueue.start();
		},

		stop: function() {
			this._started = false;
			if (this._timerId) {
				window.clearTimeout(this._timerId);
				this._timerId = null;
			}
			this._bigPlayQueue.stop();
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
