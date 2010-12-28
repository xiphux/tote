Tote = {
};

Tote.ScoreTicker = {
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
		this._initUI(hidden);
		this._update();
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
			containerDiv.css('display', 'none');
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
		
		this._updateTitle(gms.attr('y'), gms.attr('w'));

		this._updateGameTiles(gms);

		this._elements.bound.width(this._elements.gameTable.width() + 4);
	},

	_updateTitle: function(year, week)
	{
		var title = year + '-' + (year * 1 + 1) + ' week ' + week + ':';
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

				gameObj.set_year(gameslist.attr('y'));
				gameObj.set_seasonType(gameslist.attr('t'));
				gameObj.set_week(gameslist.attr('w'));

				gameObj.set_gsis(gsis);
			}
			gameObj.set_eid(g.attr('eid'));
			gameObj.set_start(g.attr('d'), g.attr('t'));
			gameObj.set_visitor(g.attr('v'));
			gameObj.set_visitorNickname(g.attr('vnn'));
			gameObj.set_home(g.attr('h'));
			gameObj.set_homeNickname(g.attr('hnn'));
			gameObj.set_status(g.attr('q'), g.attr('k'));
			gameObj.set_redZone((g.attr('rz') == "1" ? true : false));
			gameObj.set_possession(g.attr('p'));
			gameObj.set_homeScore(g.attr('hs'));
			gameObj.set_visitorScore(g.attr('vs'));

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
			if (!jQuery.inArray($(this).attr('id'), updated)) {
				$(this).parent().remove();
			}
		});
	},

	get_delay: function() {
		return this._delay;
	},

	set_delay: function(value) {
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
	ticker = new Tote.ScoreTicker.Ticker();
	ticker.initialize($('#scoreTicker'));
	ticker.start();
});
