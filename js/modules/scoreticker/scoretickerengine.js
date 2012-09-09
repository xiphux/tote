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
			if (year !== this.__year) {
				this.__year = year;
				this.__updates.year = year;
			}

			if (week !== this.__week) {
				this.__week = week;
				this.__updates.week = week;
			}

			if (type !== this.__type) {
				this.__type = type;
				this.__updates.type = type;
			}
		},

		__updateGames: function(gms)
		{
			if (!gms)
				return;

			var engine = this;
			var updated = [];

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
				if (engine.__games[gsis]) {
					game = engine.__games[gsis];
				} else {
					game = new Game();
					engine.__games[gsis] = game;
					engine.__addedGames.push(game);

					gameData.gsis = gsis;
				}

				game.set_data(gameData);

				updated[gsis] = true;
			});

			for (var gsis in this.__games) {
				if (!this.__games.hasOwnProperty(gsis))
					continue;
			
				if (updated[gsis])
					continue;

				this.__removedGames.push(this.__games[gsis]);
				delete this.__games[gsis];
			}
		},

		__updateBigPlays: function(bps)
		{
			if (!bps)
				return;

			var engine = this;
			var updated = [];

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
				if (engine.__bigPlays[id]) {
					bp = engine.__bigPlays[id];
				} else {
					bp = new BigPlay();
					engine.__bigPlays[id] = bp;
					engine.__addedBigPlays.push(bp);
				}

				bp.set_data(bpData);

				updated[id] = true;

			});

			for (var id in this.__bigPlays) {
				if (!this.__bigPlays.hasOwnProperty(id))
					continue;

				if (updated[id])
					continue;

				this.__removedBigPlays.push(this.__bigPlays[id]);
				delete this.__bigPlays[id];
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
			return this.__year + '-' + (this.__year * 1 + 1) + ' ' + (this.__type === 'P' ? 'preseason ' : '') + 'week ' + this.__week;
		},

		hasActiveGames: function()
		{
			for (var gsis in this.__games) {
				if (this.__games.hasOwnProperty(gsis)) {
					if (this.__games[gsis] && (this.__games[gsis].active())) {
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

			for (var i = 0; i < this.__observers.length; i++) {
				if (this.__observers[i] === observer)
					return;
			}

			this.__observers.push(observer);
		},

		removeObserver: function(observer)
		{
			if (!observer)
				return;

			for (var i = 0; i < this.__observers.length; i++) {
				if (this.__observers[i] === observer) {
					this.__observers.splice(i, 1);
					return;
				}
			}
		},

		__notify: function()
		{
			if (this.__observers.length === 0)
				return;

			var modified = false;
			for (var key in this.__updates) {
				if (this.__updates.hasOwnProperty(key)) {
					modified = true;
					break;
				}
			}
			if (this.__addedGames.length > 0) {
				modified = true;
				this.__updates.addedGames = this.__addedGames;
			}
			if (this.__removedGames.length > 0) {
				modified = true;
				this.__updates.removedGames = this.__removedGames;
			}
			if (this.__addedBigPlays.length > 0) {
				modified = true;
				this.__updates.addedBigPlays = this.__addedBigPlays;
			}
			if (this.__removedBigPlays.length > 0) {
				modified = true;
				this.__updates.removedBigPlays = this.__removedBigPlays;
			}
			if (!modified)
				return;

			if (this.__updates.year || this.__updates.week || this.__updates.type) {
				this.__updates.weekString = this.get_weekString();
			}

			var observer = null;
			for (var i = 0; i < this.__observers.length; i++) {
				observer = this.__observers[i];
				if (observer.observeChange) {
					this.__observers[i].observeChange(this, 'propertychanged', this.__updates);
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
