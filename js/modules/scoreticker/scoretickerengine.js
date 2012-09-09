define(['jquery', './game', './bigplay'], function($, Game, BigPlay) {
	
	function ScoreTickerEngine()
	{
		this.__games = {};
		this.__bigPlays = {};

		this.__observers = [];

		this.__updates = {};
		this.__addedGames = [];
		this.__removedGames = [];
		this.__addedBigPlays = [];
		this.__removedBigPlays = [];

		this.__refreshInterval = 15;
	}

	ScoreTickerEngine.prototype = {
		
		__started: false,

		__timer: null,
		__refreshInterval: null,

		__year: null,
		__week: null,
		__type: null,

		__games: null,
		__bigPlays: null,

		__observers: null,

		__updates: null,
		__addedGames: null,
		__removedGames: null,
		__addedBigPlays: null,
		__removedBigPlays: null,

		initialize: function()
		{
		},

		start: function()
		{
			if (this.__started)
				return;

			this.__started = true;

			this.update();

			this.__updates.started = true;
			this.__notify();
		},

		stop: function()
		{
			if (!this.__started)
				return;

			this.__started = false;

			if (this.__timer) {
				window.clearTimeout(this.__timer);
				this.__timer = null;
			}

			this.__updates.started = false;
			this.__notify();
		},

		started: function()
		{
			return this.__started;
		},

		update: function()
		{
			if (this.__timer) {
				window.clearTimeout(this.__timer);
				this.__timer = null;
			}

			$.get('scoreticker.php', {}, $.proxy(function(xml) {
				this.__updateSuccess(xml);
			}, this), 'xml');
		},

		__updateSuccess: function(xml)
		{
			var xmlData = $(xml);

			var gms = xmlData.find('gms');

			this.__updateInfo(gms.attr('y'), gms.attr('w'), gms.attr('t'));

			this.__updateGames(gms);

			this.__updateBigPlays(xmlData.find('bps'));

			if ((gms.attr('gd') == '1') && (this.hasActiveGames())) {
				this.set_refreshInterval(15);
			} else {
				this.set_refreshInterval(300);
			}

			this.__notify();
			
			if (this.__started) {
				this.__timer = window.setTimeout($.proxy(function() {
					this.update();
				}, this), this.__refreshInterval * 1000);
			}
		},

		__updateInfo: function(year, week, type)
		{
			var updates = this.__updates;

			if (year !== this.__year) {
				this.__year = year;
				updates.year = year;
			}

			if (week !== this.__week) {
				this.__week = week;
				updates.week = week;
			}

			if (type !== this.__type) {
				this.__type = type;
				updates.type = type;
			}
		},

		__updateGames: function(gms)
		{
			if (!gms)
				return;

			var updated = [];

			var games = this.__games;
			var addedGames = this.__addedGames;

			gms.find('g').each(function() {

				var g = $(this);

				var gsis = g.attr('gsis');
				if (!gsis)
					return;

				var gameData = {
					year: gms.attr('y'),
					seasonType: gms.attr('t'),
					week: gms.attr('w'),
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
				};

				var game;
				if (games[gsis]) {
					game = games[gsis];
				} else {
					game = new Game();
					games[gsis] = game;
					addedGames.push(game);

					gameData.gsis = gsis;
				}

				game.set_data(gameData);

				updated[gsis] = true;
			});

			var removedGames = this.__removedGames;

			for (var gsis in games) {
				if (!games.hasOwnProperty(gsis))
					continue;
			
				if (updated[gsis])
					continue;

				removedGames.push(games[gsis]);
				delete games[gsis];
			}
		},

		__updateBigPlays: function(bps)
		{
			if (!bps)
				return;

			var updated = [];

			var bigPlays = this.__bigPlays;
			var addedBigPlays = this.__addedBigPlays;

			bps.find('b').each(function() {

				var b = $(this);

				var id = b.attr('id');
				if (!id)
					return;
				
				var bpData = {
					gsis: b.attr('gsis'),
					eid: b.attr('eid'),
					team: b.attr('abbr'),
					message: b.attr('x'),
					id: id
				};

				var bp;
				if (bigPlays[id]) {
					bp = bigPlays[id];
				} else {
					bp = new BigPlay();
					bigPlays[id] = bp;
					addedBigPlays.push(bp);
				}

				bp.set_data(bpData);

				updated[id] = true;

			});

			var removedBigPlays = this.__removedBigPlays;

			for (var id in bigPlays) {
				if (!bigPlays.hasOwnProperty(id))
					continue;

				if (updated[id])
					continue;

				removedBigPlays.push(bigPlays[id]);
				delete bigPlays[id];
			}
		},

		get_refreshInterval: function()
		{
			return this.__refreshInterval;
		},

		set_refreshInterval: function(refreshInterval)
		{
			if (refreshInterval === this.__refreshInterval)
				return;

			this.__refreshInterval = refreshInterval;
		},

		get_weekString: function()
		{
			var year = this.__year;
			var type = this.__type;
			var week = this.__week;
			return year + '-' + (year * 1 + 1) + ' ' + (type === 'P' ? 'preseason ' : '') + 'week ' + week;
		},

		hasActiveGames: function()
		{
			var games = this.__games;
			for (var gsis in games) {
				if (games.hasOwnProperty(gsis)) {
					if (games[gsis] && (games[gsis].active())) {
						return true;
					}
				}
			}
			return false;
		},

		addObserver: function(observer)
		{
			if (!observer)
				return;

			var observers = this.__observers;
			for (var i = 0; i < observers.length; i++) {
				if (observers[i] === observer)
					return;
			}

			observers.push(observer);
		},

		removeObserver: function(observer)
		{
			if (!observer)
				return;

			var observers = this.__observers;
			for (var i = 0; i < observers.length; i++) {
				if (observers[i] === observer) {
					observers.splice(i, 1);
					return;
				}
			}
		},

		__notify: function()
		{
			var observers = this.__observers;

			if (observers.length === 0)
				return;

			var modified = false;

			var updates = this.__updates;

			if (this.__addedGames.length > 0) {
				modified = true;
				updates.addedGames = this.__addedGames;
			}
			if (this.__removedGames.length > 0) {
				modified = true;
				updates.removedGames = this.__removedGames;
			}
			if (this.__addedBigPlays.length > 0) {
				modified = true;
				updates.addedBigPlays = this.__addedBigPlays;
			}
			if (this.__removedBigPlays.length > 0) {
				modified = true;
				updates.removedBigPlays = this.__removedBigPlays;
			}
			if (!modified) {
				for (var key in updates) {
					if (updates.hasOwnProperty(key)) {
						modified = true;
						break;
					}
				}
			}
			if (!modified)
				return;

			if (updates.year || updates.week || updates.type) {
				updates.weekString = this.get_weekString();
			}

			var observer = null;
			for (var i = 0; i < observers.length; i++) {
				observer = observers[i];
				if (typeof observer.observeChange === 'function') {
					observer.observeChange(this, 'propertychanged', updates);
				}
			}

			this.__updates = {};
			this.__addedGames = [];
			this.__removedGames = [];
			this.__addedBigPlays = [];
			this.__removedBigPlays = [];
		}

	};

	return ScoreTickerEngine;

});
