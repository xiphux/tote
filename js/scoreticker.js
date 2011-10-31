Tote = {
};

Tote.ScoreTicker = {
};

Tote.ScoreTicker.BigPlay = function()
{
	this._data = {
		eid: null,
		gsis: null,
		id: null,
		team: null,
		message: null
	};
};

Tote.ScoreTicker.BigPlay.prototype = {

	get_eid: function()
	{
		return this._data.eid;
	},

	set_eid: function(eid)
	{
		this._data.eid = eid;
	},

	get_gsis: function()
	{
		return this._data.gsis;
	},

	set_gsis: function(gsis)
	{
		this._data.gsis = gsis;
	},

	get_id: function()
	{
		return this._data.id;
	},

	set_id: function(id)
	{
		this._data.id = id;
	},

	get_team: function()
	{
		return this._data.team;
	},

	set_team: function(team)
	{
		this._data.team = team;
	},

	get_message: function()
	{
		return this._data.message;
	},

	set_message: function(msg)
	{
		this._data.message = msg;
	},

	set_data: function(data)
	{
		for (var prop in data) {
			switch (prop) {
				case 'eid':
					this.set_eid(data.eid);
					break;
				case 'gsis':
					this.set_gsis(data.gsis);
					break;
				case 'id':
					this.set_id(data.id);
					break;
				case 'team':
					this.set_team(data.team);
					break;
				case 'message':
					this.set_message(data.message);
					break;
			}
		}
	}

};

Tote.ScoreTicker.Game = function()
{
	this._data = {
		year: null,
		seasonType: null,
		week: null,
		gsis: null,
		eid: null,
		startDay: null,
		startTime: null,
		visitor: null,
		visitorNickname: null,
		home: null,
		homeNickname: null,
		homeScore: null,
		visitorScore: null,
		redZone: false,
		quarter: null,
		clock: null,
		possession: null
	};

	this._elements = {
		base: null,
		table: null,
		visitorCell: null,
		visitorPossessionCell: null,
		visitorScoreCell: null,
		homeCell: null,
		homePossessionCell: null,
		homeScoreCell: null,
		statusCell: null
	};

};

Tote.ScoreTicker.Game.CSSClasses = {
	tile: 'tickerGameTile',
	redZone: 'tickerGameRedZone',
	pending: 'tickerGamePending',
	finished: 'tickerGameFinished',
	playing: 'tickerPlaying',
	
	team: 'tickerGameTeam',
	score: 'tickerGameScore',
	status: 'tickerGameStatus',
	possession: 'tickerPossession',
	winner: 'tickerTeamWinner'
};

Tote.ScoreTicker.Game.prototype = {

	_initialized: false,

	initialize: function()
	{
		this._elements.base = jQuery(document.createElement('a'));
		this._buildElement();
		this._initialized = true;
	},

	_buildElement: function()
	{
		var table = jQuery(document.createElement('table'));
		this._elements.table = table;
		table.addClass(Tote.ScoreTicker.Game.CSSClasses.tile);
		if (this._data.redZone) {
			table.addClass(Tote.ScoreTicker.Game.CSSClasses.redZone);
		}


		var visitorRow = jQuery(document.createElement('tr'));

		var visitorCell = jQuery(document.createElement('td'));
		this._elements.visitorCell = visitorCell;
		visitorCell.addClass(Tote.ScoreTicker.Game.CSSClasses.team);
		visitorCell.text(this._data.visitor);
		visitorRow.append(visitorCell);

		var visitorPossessionCell = jQuery(document.createElement('td'));
		this._elements.visitorPossessionCell = visitorPossessionCell;
		visitorPossessionCell.addClass(Tote.ScoreTicker.Game.CSSClasses.possession);
		if (this.is_playing() && (this._data.possession == this._data.visitor)) {
			visitorPossessionCell.text('<');
		}
		visitorRow.append(visitorPossessionCell);

		var visitorScoreCell = jQuery(document.createElement('td'));
		this._elements.visitorScoreCell = visitorScoreCell;
		visitorScoreCell.addClass(Tote.ScoreTicker.Game.CSSClasses.score);
		if (this._data.quarter != 'P') {
			visitorScoreCell.text(this._data.visitorScore);
		}
		visitorRow.append(visitorScoreCell);
		
		table.append(visitorRow);


		var homeRow = jQuery(document.createElement('tr'));

		var homeCell = jQuery(document.createElement('td'));
		this._elements.homeCell = homeCell;
		homeCell.addClass(Tote.ScoreTicker.Game.CSSClasses.team);
		homeCell.text(this._data.home);
		homeRow.append(homeCell);

		var homePossessionCell = jQuery(document.createElement('td'));
		this._elements.homePossessionCell = homePossessionCell;
		homePossessionCell.addClass(Tote.ScoreTicker.Game.CSSClasses.possession);
		if (this.is_playing() && (this._data.possession == this._data.home)) {
			homePossessionCell.text('<');
		}
		homeRow.append(homePossessionCell);

		var homeScoreCell = jQuery(document.createElement('td'));
		this._elements.homeScoreCell = homeScoreCell;
		homeScoreCell.addClass(Tote.ScoreTicker.Game.CSSClasses.score);
		if (this._data.quarter != 'P') {
			homeScoreCell.text(this._data.homeScore);
		}
		homeRow.append(homeScoreCell);
		
		table.append(homeRow);


		var statusRow = jQuery(document.createElement('tr'));

		var statusCell = jQuery(document.createElement('td'));
		this._elements.statusCell = statusCell;
		statusCell.addClass(Tote.ScoreTicker.Game.CSSClasses.status);
		statusCell.attr('colspan', '3');
		statusCell.text(this._buildStatus());
		statusRow.append(statusCell);

		table.append(statusRow);		


		var a = this._elements.base;
		a.attr('href', this._buildUrl());
		a.attr('target', '_blank');
		a.attr('id', this._data.gsis);
		a.append(table);

		this._updateStatus();
		this._updateWinner();
	},

	_buildStatus: function()
	{
		switch (this._data.quarter) {

			case 'P':
				// pending game
				return this._data.day + ' ' + this._data.time;

			case 'F':
				// final
				return 'Final';

			case 'FO':
				// final overtime
				return 'Final OT';

			case 'H':
				// half
				return 'Halftime';

			case '1':
			case '2':
			case '3':
			case '4':
				// regulation quarter
				return 'Q' + this._data.quarter + ' ' + this._data.clock;

			default:
				// quarter other than regulation (overtime)
				return 'OT ' + this._data.clock;
		}
	},

	_buildUrl: function()
	{
		var typestr = '';
		switch (this._data.seasonType) {
			case 'P':
				typestr = 'PRE';
				break;
			case 'R':
				typestr = 'REG';
				break;
			default:
				return '';
		}

		var url = 'http://www.nfl.com/gamecenter/' + this._data.eid + '/' + this._data.year + '/' + typestr + '' + this._data.week + '/' + this._data.visitorNickname + '@' + this._data.homeNickname;
		return url;
	},

	_updateWinner: function()
	{
		var visitorwin = false;
		var homewin = false;

		if ((this._data.quarter == 'F') || (this._data.quarter == 'FO')) {
			var vs = this._data.visitorScore * 1;
			var hs = this._data.homeScore * 1;
			if (hs > vs) {
				homewin = true;
			} else if (vs > hs) {
				visitorwin = true;
			}
		}

		if (visitorwin) {
			this._elements.visitorCell.addClass(Tote.ScoreTicker.Game.CSSClasses.winner);
			this._elements.visitorScoreCell.addClass(Tote.ScoreTicker.Game.CSSClasses.winner);
		} else {
			this._elements.visitorCell.removeClass(Tote.ScoreTicker.Game.CSSClasses.winner);
			this._elements.visitorScoreCell.removeClass(Tote.ScoreTicker.Game.CSSClasses.winner);
		}

		if (homewin) {
			this._elements.homeCell.addClass(Tote.ScoreTicker.Game.CSSClasses.winner);
			this._elements.homeScoreCell.addClass(Tote.ScoreTicker.Game.CSSClasses.winner);
		} else {
			this._elements.homeCell.removeClass(Tote.ScoreTicker.Game.CSSClasses.winner);
			this._elements.homeScoreCell.removeClass(Tote.ScoreTicker.Game.CSSClasses.winner);
		}
	},

	_updateStatus: function()
	{
		var table = this._elements.table;
		switch (this._data.quarter) {
			case 'P':
				table.removeClass(Tote.ScoreTicker.Game.CSSClasses.playing);
				table.removeClass(Tote.ScoreTicker.Game.CSSClasses.finished);
				table.addClass(Tote.ScoreTicker.Game.CSSClasses.pending);
				break;
			case 'F':
			case 'FO':
				table.removeClass(Tote.ScoreTicker.Game.CSSClasses.playing);
				table.removeClass(Tote.ScoreTicker.Game.CSSClasses.pending);
				table.addClass(Tote.ScoreTicker.Game.CSSClasses.finished);
				break;
			default:
				table.removeClass(Tote.ScoreTicker.Game.CSSClasses.finished);
				table.removeClass(Tote.ScoreTicker.Game.CSSClasses.pending);
				table.addClass(Tote.ScoreTicker.Game.CSSClasses.playing);
				if (!this._elements.homeScoreCell.text()) {
					this._elements.homeScoreCell.text(this._data.homeScore);
				}
				if (!this._elements.visitorScoreCell.text()) {
					this._elements.visitorScoreCell.text(this._data.visitorScore);
				}
				break;
		}
		this._updateWinner();
	},

	get_element: function()
	{
		return this._elements.base;
	},

	get_year: function()
	{
		return this._data.year;
	},

	set_year: function(year)
	{
		if (this._data.year == year) {
			return;
		}

		this._data.year = year;

		if (this._initialized) {
			this._elements.base.attr('href', this._buildUrl());
		}
	},

	get_seasonType: function()
	{
		return this._data.seasonType;
	},

	set_seasonType: function(type)
	{
		if (this._data.seasonType == type) {
			return;
		}

		this._data.seasonType = type;
		
		if (this._initialized) {
			this._elements.base.attr('href', this._buildUrl());
		}
	},

	get_week: function()
	{
		return this._data.week;
	},

	set_week: function(week)
	{
		if (this._data.week == week) {
			return;
		}

		this._data.week = week;

		if (this._initialized) {
			this._elements.base.attr('href', this._buildUrl());
		}
	},

	get_gsis: function()
	{
		return this._data.gsis;
	},

	set_gsis: function(gsis)
	{
		if (this._initialized) {
			// can't change game id after initialization
			return;
		}

		this._data.gsis = gsis;
	},

	get_eid: function()
	{
		return this._data.eid;
	},

	set_eid: function(eid)
	{
		if (this._data.eid == eid) {
			return;
		}

		this._data.eid = eid;

		if (this._initialized) {
			this._elements.base.attr('href', this._buildUrl());
		}
	},

	get_start: function()
	{
		return { day: this._data.day, time: this._data.time };
	},

	set_start: function(day, time)
	{
		var changed = false;

		if (this._data.day != day) {
			this._data.day = day;
			changed = true;
		}

		if (this._data.time != time) {
			this._data.time = time;
			changed = true;
		}

		if (changed && this._initialized) {
			if (this._data.quarter == 'P') {
				this._elements.statusCell.text(this._buildStatus());
			}
		}
	},

	get_visitor: function()
	{
		return this._data.visitor;
	},

	set_visitor: function(visitor)
	{
		if (this._data.visitor == visitor) {
			return;
		}

		this._data.visitor = visitor;

		if (this._initialized) {
			this._elements.visitorCell.text(visitor);
		}
	},

	get_visitorNickname: function()
	{
		return this._data.visitorNickname;
	},

	set_visitorNickname: function(nick)
	{
		if (this._data.visitorNickname == nick) {
			return;
		}

		this._data.visitorNickname = nick;

		if (this._initialized) {
			this._elements.base.attr('href', this._buildUrl());
		}
	},

	get_home: function()
	{
		return this._data.home;
	},

	set_home: function(home)
	{
		if (this._data.home == home) {
			return;
		}

		this._data.home = home;

		if (this._initialized) {
			this._elements.homeCell.text(home);
		}
	},

	get_homeNickname: function()
	{
		return this._data.homeNickname;
	},

	set_homeNickname: function(nick)
	{
		if (this._data.homeNickname == nick) {
			return;
		}

		this._data.homeNickname = nick;

		if (this._initialized) {
			this._elements.base.attr('href', this._buildUrl());
		}
	},

	get_homeScore: function()
	{
		return this._data.homeScore;
	},

	set_homeScore: function(score)
	{
		if (this._data.homeScore === score) {
			return;
		}

		this._data.homeScore = score;

		if (this._initialized) {
			if (this._data.quarter != 'P') {
				this._elements.homeScoreCell.text(score);
				if ((this._data.quarter == 'F') || (this._data.quarter == 'FO')) {
					this._updateWinner();
				}
			} else {
				this._elements.homeScoreCell.text('');
			}
		}
	},

	get_visitorScore: function()
	{
		return this._data.visitorScore;
	},

	set_visitorScore: function(score)
	{
		if (this._data.visitorScore === score) {
			return;
		}

		this._data.visitorScore = score;

		if (this._initialized) {
			if (this._data.quarter != 'P') {
				this._elements.visitorScoreCell.text(score);
				if ((this._data.quarter == 'F') || (this._data.quarter == 'FO')) {
					this._updateWinner();
				}
			} else {
				this._elements.visitorScoreCell.text('');
			}
		}
	},

	get_redZone: function()
	{
		return this._data.redZone;
	},

	set_redZone: function(redZone)
	{
		if (this._data.redZone == redZone) {
			return;
		}

		this._data.redZone = redZone;

		if (this._initialized) {
			if (redZone && this.is_playing()) {
				this._elements.table.addClass(Tote.ScoreTicker.Game.CSSClasses.redZone);
			} else {
				this._elements.table.removeClass(Tote.ScoreTicker.Game.CSSClasses.redZone);
			}
		}
	},

	get_status: function()
	{
		return { quarter: this._data.quarter, time: this._data.time };
	},

	set_status: function(quarter, clock)
	{
		var quarterchanged = false;
		var clockchanged = false;

		if (this._data.quarter != quarter) {
			this._data.quarter = quarter;
			quarterchanged = true;
		}

		if (this._data.clock != clock) {
			this._data.clock = clock;
			clockchanged = true;
		}

		if (this._initialized && (quarterchanged || (clockchanged && (this.is_playing())))) {
			this._elements.statusCell.text(this._buildStatus());
			if (quarterchanged) {
				this._updateStatus();
			}
		}
	},

	get_possession: function()
	{
		return this._data.possession;
	},

	set_possession: function(possession)
	{
		if (this._data.possession == possession) {
			return;
		}

		this._data.possession = possession;

		if (this._initialized) {
			if (this.is_playing() && (possession == this._data.visitor)) {
				this._elements.visitorPossessionCell.text('<');
			} else {
				this._elements.visitorPossessionCell.text('');
			}

			if (this.is_playing() && (possession == this._data.home)) {
				this._elements.homePossessionCell.text('<');
			} else {
				this._elements.homePossessionCell.text('');
			}
		}
	},

	is_playing: function()
	{
		var q = this._data.quarter;
		if ((q != null) && (q != 'P') && (q != 'F') && (q != 'FO') && (q != 'H')) {
			return true;
		}
		return false;
	},

	is_active: function()
	{
		return (this.is_playing() || (this._data.quarter == 'H'));
	},

	set_data: function(data)
	{
		for (var prop in data) {
			switch (prop) {
				case 'year':
					this.set_year(data.year);
					break;
				case 'seasonType':
					this.set_seasonType(data.seasonType);
					break;
				case 'week':
					this.set_week(data.week);
					break;
				case 'gsis':
					this.set_gsis(data.gsis);
					break;
				case 'eid':
					this.set_eid(data.eid);
					break;
				case 'start':
					if (('day' in data.start) && ('time' in data.start)) {
						this.set_start(data.start.day, data.start.time);
					}
					break;
				case 'visitor':
					this.set_visitor(data.visitor);
					break;
				case 'visitorNickname':
					this.set_visitorNickname(data.visitorNickname);
					break;
				case 'home':
					this.set_home(data.home);
					break;
				case 'homeNickname':
					this.set_homeNickname(data.homeNickname);
					break;
				case 'homeScore':
					this.set_homeScore(data.homeScore);
					break;
				case 'visitorScore':
					this.set_visitorScore(data.visitorScore);
					break;
				case 'redZone':
					this.set_redZone(data.redZone);
					break;
				case 'status':
					if (('quarter' in data.status) && ('clock' in data.status)) {
						this.set_status(data.status.quarter, data.status.clock);
					}
					break;
				case 'possession':
					this.set_possession(data.possession);
					break;
			}
		}
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
		gameRow: null,
		bigPlay: null
	};

	this._gameObjects = {};

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
			ticker._elements.gameTable.animate({marginLeft: -ticker._elements.gameTable.outerWidth()}, 'fast', function() {
				ticker._elements.containerDiv.hide('fast');
				ticker._elements.bound.addClass('rounded-bottom');
				ticker._elements.toggleLink.text(Tote.ScoreTicker.Ticker.Labels.showLink);
				ticker._elements.toggleLink.removeClass(Tote.ScoreTicker.Ticker.CSSClasses.open);
				ticker._elements.toggleLink.addClass(Tote.ScoreTicker.Ticker.CSSClasses.closed);
			});
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

		var bpPanel = jQuery(document.createElement('div'));
		bpPanel.width(0);
		bpPanel.css('opacity', 0.25);
		bpPanel.css('z-index', 5);
		bpPanel.css('position', 'absolute');
		bpPanel.css('overflow', 'hidden');
		bpPanel.addClass('tickerBigPlay');

		var bpContent = jQuery(document.createElement('div'));
		bpContent.width(100);
		bpContent.css('height', '100%');
		bpContent.addClass('tickerBigPlayContent');
		bpPanel.append(bpContent);

		containerDiv.append(bpPanel);

		this._elements.bigPlay = bpPanel;
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
		var gms = $(xml).find('gms');
		
		this._updateTitle(gms.attr('y'), gms.attr('w'), gms.attr('t'));

		this._updateGameTiles(gms);

		var bps = $(xml).find('bps');

		this._updateBigPlays(bps);

		this._elements.bound.width(this._elements.gameTable.width() + 4);

		if ((gms.attr('gd') == '1') && (this._hasActiveGames())) {
			this.set_delay(15);
		} else {
			this.set_delay(300);
		}
	},

	_updateTitle: function(year, week, type)
	{
		var title = year + '-' + (year * 1 + 1) + ' ' + (type == 'P' ? 'preseason ' : '') + 'week ' + week + ':';
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
			var created = false;

			if (instance._gameObjects[gsis]) {
				gameObj = instance._gameObjects[gsis];
			} else {
				gameObj = new Tote.ScoreTicker.Game();
				created = true;

				gameObj.set_data({
					year: gameslist.attr('y'),
					seasonType: gameslist.attr('t'),
					week: gameslist.attr('w'),
					gsis: gsis
				});
			}

			gameObj.set_data({
				eid: g.attr('eid'),
				start: {
					day: g.attr('d'),
					time: g.attr('t')
				},
				visitor: g.attr('v'),
				visitorNickname: g.attr('vnn'),
				home: g.attr('h'),
				homeNickname: g.attr('hnn'),
				status: {
					quarter: g.attr('q'),
					clock: g.attr('k')
				},
				redZone: (g.attr('rz') == '1' ? true : false),
				possession: g.attr('p'),
				homeScore: g.attr('hs'),
				visitorScore: g.attr('vs')
			});

			if (created) {
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
			if (jQuery.inArray($(this).attr('id'), updated) == -1) {
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

		var startBigPlays = false;

		if (this._bigPlayQueue.length < 1) {
			startBigPlays = true;
		}

		var added = false;
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

			var bpObj = new Tote.ScoreTicker.BigPlay();
			bpObj.set_data({
				id: id,
				gsis: b.attr('gsis'),
				eid: b.attr('eid'),
				team: b.attr('abbr'),
				message: b.attr('x')
			});

			ticker._bigPlayQueue.push(bpObj);
			added = true;

		});

		if (startBigPlays && added) {
			this._showNextBigPlay();
		}
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
		for (var gsis in this._gameObjects) {
			if (this._gameObjects.hasOwnProperty(gsis)) {
				count++;
			}
		}
		var half = Math.ceil(count/2);
		var idx = 0;

		for (var gsis in this._gameObjects) {
			if (this._gameObjects[gsis]) {
				idx++;
				if (gsis == bpObj.get_gsis()) {
					var gameElem = this._gameObjects[gsis].get_element();
					var pos = gameElem.position();
					var bpPos = pos.left;
					var anim = {
						width: '100px',
						opacity: 1
					};
					if (idx < half) {
						bpPos += gameElem.width();
					} else {
						anim.left = (bpPos - 100) + "px";
					}
					this._elements.bigPlay.css('left', bpPos + "px");
					this._elements.bigPlay.css('top', pos.top + "px");
					this._elements.bigPlay.height(this._elements.gameTable.height());
					this._elements.bigPlay.find('.tickerBigPlayContent').html(bpObj.get_team() + "<br />" + bpObj.get_message());
					this._elements.bigPlay.animate(anim, 400);
					if (idx < half) {
						window.setTimeout($.proxy(function() { this._bigPlayFinished(false); }, this), 10000);
					} else {
						window.setTimeout($.proxy(function() { this._bigPlayFinished(true); }, this), 10000);
					}
					return;
				}
			}
		}
	},

	_bigPlayFinished: function(left)
	{
		var anim = {
			width: '0px',
			opacity: 0.25
		};
		if (left) {
			anim.left = (this._elements.bigPlay.position().left + 100) + "px";
		}
		this._elements.bigPlay.animate(anim, 400);
		if (this._bigPlayQueue.length > 0) {
			this._bigPlayQueue.shift();
		}
		if (this._bigPlayQueue.length > 0) {
			this._showNextBigPlay();
		}
	},

	_hasActiveGames: function()
	{
		for (var gsis in this._gameObjects) {
			if (this._gameObjects[gsis] && (this._gameObjects[gsis].is_active())) {
				return true;
			}
		}
		return false;
	},

	get_delay: function() {
		return this._delay;
	},

	set_delay: function(value) {
		if (this._delay == value) {
			return;
		}

		this._delay = value;
		if (this._started) {
			this.stop();
			this.start();
		}
	},

	start: function() {
		this._started = true;
		if (!this._timerId) {
			this._update();
			this._timerId = window.setInterval($.proxy(function() { this._update(); }, this), this._delay * 1000);
		}
	},

	stop: function() {
		this._started = false;
		if (this._timerId) {
			window.clearInterval(this._timerId);
			this._timerId = null;
		}
	},

	get_started: function() {
		return this._started;
	}

};

var ticker = null;
$(document).ready(function() {
	var hidden = false;
	if ($.cookies.test()) {
		var ck = $.cookies.get('ToteScoretickerHidden');
		if (ck !== null) {
			hidden = ck;
		}
	}
	ticker = new Tote.ScoreTicker.Ticker();
	ticker.initialize($('#scoreTicker'), hidden);
});
