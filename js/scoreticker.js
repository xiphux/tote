Tote = {
};

Tote.ScoreTicker = function()
{
	var url = window.location.href.match(/^([^\?]+\/)/);
        if (url) {
                this._urls.base = url[1];
        }
};

Tote.ScoreTicker.prototype = {

	_urls: {
		base: null,
		ticker: 'scoreticker.php'
	},
	_elements: {
		bound: null,
		containerDiv: null,
		toggleDiv: null,
		toggleLink: null,
		titleDiv: null,
		gameTable: null,
		gameRow: null
	},
	_classes: {
		toggleLink: 'tickerToggleLink',
		toggleDiv: 'tickerToggleDiv',

		titleDiv: 'tickerTitle',
		containerDiv: 'tickerContainerDiv',

		closed: 'tickerClosed',
		open: 'tickerOpen',

		gameTable: 'tickerGameTable',
		gameCell: 'tickerGameCell',
		gameTile: 'tickerGameTile',

		gameRedZone: 'tickerGameRedZone',
		gamePending: 'tickerGamePending',
		gameFinished: 'tickerGameFinished',
		gamePlaying: 'tickerPlaying',
		
		gameTeam: 'tickerGameTeam',
		gameScore: 'tickerGameScore',
		gameStatus: 'tickerGameStatus',
		possession: 'tickerPossession',
		teamWinner: 'tickerTeamWinner'
	},
	_labels: {
		showLink: 'Score ticker…',
		hideLink: 'Score ticker'
	},
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
		toggleDiv.addClass(this._classes.toggleDiv);
		var toggleLink = jQuery(document.createElement('a'));
		toggleLink.addClass(this._classes.toggleLink);
		if (hidden) {
			toggleLink.text(this._labels.showLink);
			toggleLink.addClass(this._classes.closed);
		} else {
			toggleLink.text(this._labels.hideLink);
			toggleLink.addClass(this._classes.open);
		}
		toggleLink.attr('href', '#');
		var ticker = this;
		var showCallback = function(event) {
			ticker.start();
			ticker._elements.bound.removeClass('rounded-bottom');
			ticker._elements.containerDiv.show('fast', function() {
				ticker._elements.gameTable.animate({marginLeft: 0}, 'fast');
				ticker._elements.toggleLink.text(ticker._labels.hideLink);
				ticker._elements.toggleLink.removeClass(ticker._classes.closed);
				ticker._elements.toggleLink.addClass(ticker._classes.open);
			});
			return false;
		};
		var hideCallback = function(event) {
			ticker.stop();
			ticker._elements.gameTable.animate({marginLeft: -ticker._elements.gameTable.outerWidth()}, 'fast', function() {
				ticker._elements.containerDiv.hide('fast');
				ticker._elements.bound.addClass('rounded-bottom');
				ticker._elements.toggleLink.text(ticker._labels.showLink);
				ticker._elements.toggleLink.removeClass(ticker._classes.open);
				ticker._elements.toggleLink.addClass(ticker._classes.closed);
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
		containerDiv.addClass(this._classes.containerDiv);
		containerDiv.addClass('subSection');
		if (hidden) {
			containerDiv.css('display', 'none');
		}

		var titleDiv = jQuery(document.createElement('div'));
		titleDiv.addClass(this._classes.titleDiv);
		containerDiv.append(titleDiv);
		this._elements.titleDiv = titleDiv;
		
		var gameTable = jQuery(document.createElement('table'));
		gameTable.addClass(this._classes.gameTable);

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
		$.get(this._urls.ticker, {}, $.proxy(function (xml) { this._updateSuccess(xml); }, this), 'xml');
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
			var tile = instance._createGameTile($(this), gameslist.attr('y'), gameslist.attr('t'), gameslist.attr('w'));
			
			var existing = instance._elements.gameRow.find('#' + $(this).attr('gsis'));
			if (existing.size() > 0) {
				existing.replaceWith(tile);
			} else {
				var td = jQuery(document.createElement('td'));
				td.addClass(instance._classes.gameCell);
				td.append(tile);
				instance._elements.gameRow.append(td);
			}
			updated.push($(this).attr('gsis'));
		});

		this._elements.gameRow.find('.' + this._classes.gameTile).each(function() {
			if (!jQuery.inArray($(this).attr('id'), updated)) {
				$(this).parent().remove();
			}
		});
	},

	_createGameTile: function(gamenode, year, seasontype, week)
	{
		var table = jQuery(document.createElement('table'));
		table.addClass(this._classes.gameTile);

		var visitorwin = false;
		var homewin = false;
		if ((gamenode.attr('q') == 'F') || (gamenode.attr('q') == 'FO')) {
			if ((gamenode.attr('vs') * 1) > (gamenode.attr('hs') * 1)) {
				visitorwin = true;
			} else if ((gamenode.attr('hs') * 1) > (gamenode.attr('vs') * 1)) {
				homewin = true;
			}
		}

		var vrow = this._buildTeamRow(gamenode.attr('v'), (gamenode.attr('q') != 'P') ? gamenode.attr('vs') : null, visitorwin, (gamenode.attr('p') == gamenode.attr('v')));
		table.append(vrow);

		var hrow = this._buildTeamRow(gamenode.attr('h'), (gamenode.attr('q') != 'P') ? gamenode.attr('hs') : null, homewin, (gamenode.attr('p') == gamenode.attr('h')));
		table.append(hrow);
		
		var srow = this._buildStatusRow(gamenode.attr('q'), gamenode.attr('d'), gamenode.attr('t'), gamenode.attr('k'));
		table.append(srow);
	
	
		if (gamenode.attr('rz') == "1") {
			table.addClass(this._classes.gameRedZone);
		}

		switch (gamenode.attr('q')) {

			case 'P':
				table.addClass(this._classes.gamePending);
				break;

			case 'F':
			case 'FO':
				table.addClass(this._classes.gameFinished);
				break;

			default:
				table.addClass(this._classes.gamePlaying);
				break;	
		}

		var a = jQuery(document.createElement('a'));
		a.attr('href', this._buildGameUrl(gamenode.attr('eid'), year, seasontype, week, gamenode.attr('vnn'), gamenode.attr('hnn')));
		a.attr('target', '_blank');
		a.attr('id', gamenode.attr('gsis'));
		a.append(table);

		return a;
	},

	_buildTeamRow: function(team, score, win, possession)
	{
		var row = jQuery(document.createElement('tr'));
		if (win) {
			row.addClass(this._classes.teamWinner);
		}

		// team
		var cell = jQuery(document.createElement('td'));
		cell.addClass(this._classes.gameTeam);
		cell.text(team);
		if (possession) {
			var span = jQuery(document.createElement('span'));
			span.addClass(this._classes.possession);
			span.text('<');
			cell.append(span);
		}
		row.append(cell);

		// score
		cell = jQuery(document.createElement('td'));
		cell.addClass(this._classes.gameScore);
		if (score != null) {
			cell.text(score);
		}
		row.append(cell);

		return row;
	},

	_buildStatusRow: function(quarter, day, time, gameclock)
	{
		var row = jQuery(document.createElement('tr'));

		var cell = jQuery(document.createElement('td'));
		cell.addClass(this._classes.gameStatus);
		cell.attr('colspan', '2');

		switch (quarter) {

			case 'P':
				// pending game
				cell.text(day + ' ' + time);
				break;

			case 'F':
				// final
				cell.text('Final');
				break;

			case 'FO':
				// final overtime
				cell.text('Final OT');
				break;

			case 'H':
				// halftime
				cell.text('Halftime');
				break;

			case '1':
			case '2':
			case '3':
			case '4':
				// some quarter
				cell.text('Q' + quarter + ' ' + gameclock);
				break;

			default:
				// some quarter above 4 (overtime)
				cell.text('OT ' + gameclock);
				break;	
		}

		row.append(cell);

		return row;
	},

	_buildGameUrl: function(eid, year, seasontype, week, visitornickname, homenickname)
	{
		var typestr = '';
		switch (seasontype) {
			case 'P':
				typestr = 'PRE';
				break;
			case 'R':
				typestr = 'REG';
				break;
			default:
				return '';
		}

		var url = 'http://www.nfl.com/gamecenter/' + eid + '/' + year + '/' + typestr + '' + week + '/' + visitornickname + '@' + homenickname;
		return url;
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
	},

};

var ticker = null;
$(document).ready(function() {
	ticker = new Tote.ScoreTicker();
	ticker.initialize($('#scoreTicker'));
	ticker.start();
});
