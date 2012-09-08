define(['jquery', 'modules/scoreticker/game'], function($, Game) {

	function GameTile(game) {
		if (game) {
			this.__game = game;
			game.addObserver(this);
		}
	}

	GameTile.prototype = {
		
		__game: null,

		__base: null,
		__table: null,
		__visitorCell: null,
		__visitorPossessionCell: null,
		__visitorScoreCell: null,
		__homeCell: null,
		__homePossessionCell: null,
		__homeScoreCell: null,
		__statusCell: null,

		get_game: function()
		{
			return this.__game;
		},

		get_element: function()
		{
			return this.__base;
		},

		get_position: function()
		{
			return this.__table.position();
		},

		get_width: function()
		{
			return this.__table.width();
		},

		get_height: function()
		{
			return this.__table.height();
		},

		initialize: function()
		{
			this.__initElements();
			this.__initState();
		},

		__initElements: function()
		{
			var a = $(document.createElement('a'));
			this.__base = a;

			var table = $(document.createElement('table'));
			this.__table = table;
			table.addClass('tickerGameTile');

			var visitorRow = $(document.createElement('tr'));

			var visitorCell = $(document.createElement('td'));
			this.__visitorCell = visitorCell;
			visitorCell.addClass('tickerGameTeam');
			visitorRow.append(visitorCell);

			var visitorPossessionCell = $(document.createElement('td'));
			this.__visitorPossessionCell = visitorPossessionCell;
			visitorPossessionCell.addClass('tickerPossession');
			visitorRow.append(visitorPossessionCell);

			var visitorScoreCell = $(document.createElement('td'));
			this.__visitorScoreCell = visitorScoreCell;
			visitorScoreCell.addClass('tickerGameScore');
			visitorRow.append(visitorScoreCell);

			table.append(visitorRow);


			var homeRow = $(document.createElement('tr'));

			var homeCell = $(document.createElement('td'));
			this.__homeCell = homeCell;
			homeCell.addClass('tickerGameTeam');
			homeRow.append(homeCell);

			var homePossessionCell = $(document.createElement('td'));
			this.__homePossessionCell = homePossessionCell;
			homePossessionCell.addClass('tickerPossession');
			homeRow.append(homePossessionCell);

			var homeScoreCell = $(document.createElement('td'));
			this.__homeScoreCell = homeScoreCell;
			homeScoreCell.addClass('tickerGameScore');
			homeRow.append(homeScoreCell);

			table.append(homeRow);

			var statusRow = $(document.createElement('tr'));

			var statusCell = $(document.createElement('td'));
			this.__statusCell = statusCell;
			statusCell.addClass('tickerGameStatus');
			statusCell.attr('colspan', '3');
			statusRow.append(statusCell);

			table.append(statusRow);

			a.attr('target', '_blank');
			a.append(table);
		},

		__initState: function()
		{
			if (!this.__game)
				return;

			var game = this.__game;

			if (game.get_redZone()) {
				this.__table.addClass('tickerGameRedZone');
			}

			this.__visitorCell.text(game.get_visitor());

			if (game.playing() && (game.get_possession() === game.get_visitor())) {
				this.__visitorPossessionCell.text('<');
			}

			if (game.get_quarter() !== 'P') {
				this.__visitorScoreCell.text(game.get_visitorScore());
			}

			this.__homeCell.text(game.get_home());

			if (game.playing() && (game.get_possession() === game.get_home())) {
				this.__homePossessionCell.text('<');
			}

			if (game.get_quarter() !== 'P') {
				this.__homeScoreCell.text(game.get_homeScore());
			}

			this.__statusCell.text(game.get_status());

			this.__base.attr('href', game.get_url());
			this.__base.attr('id', game.get_gsis());

			this.__updateQuarter(game.get_quarter());
		},

		observeChange: function(object, changeType, changeData)
		{
			if (object !== this.__game)
				return;

			if (changeType !== 'propertychanged')
				return;

			if (!changeData)
				return;

			for (var key in changeData) {
				if (!changeData.hasOwnProperty(key))
					continue;

				switch (key) {
					case 'status':
						this.__statusCell.text(changeData.status);
						break;
					case 'url':
						this.__base.attr('href', changeData.url);
						break;
					case 'visitor':
						this.__visitorCell.text(changeData.visitor);
						break;
					case 'home':
						this.__homeCell.text(changeData.home);
						break;
					case 'homeScore':
						if (this.__game.get_quarter() !== 'P') {
							this.__homeScoreCell.text(changeData.homeScore);
						}
						break;
					case 'visitorScore':
						if (this.__game.get_quarter() !== 'P') {
							this.__visitorScoreCell.text(changeData.visitorScore);
						}
						break;
					case 'redZone':
						if (changeData.redZone) {
							this.__table.addClass('tickerGameRedZone');
						} else {
							this.__table.removeClass('tickerGameRedZone');
						}
						break;
					case 'quarter':
						this.__updateQuarter(changeData.quarter);
						break;
					case 'possession':
						if (this.__game.playing() && (changeData.possession === this.__game.get_visitor())) {
							this.__visitorPossessionCell.text('<');
						} else {
							this.__visitorPossessionCell.text('');
						}
						if (this.__game.playing() && (changeData.possession === this.__game.get_home())) {
							this.__homePossessionCell.text('<');
						} else {
							this.__homePossessionCell.text('');
						}
						break;
				}
			}
		},

		__updateQuarter: function(quarter)
		{
			if (!this.__game)
				return;

			var game = this.__game;
			var table = this.__table;
			switch (quarter) {
				case 'P':
					table.removeClass('tickerPlaying');
					table.removeClass('tickerGameFinished');
					table.addClass('tickerGamePending');
					break;
				case 'F':
				case 'FO':
					table.removeClass('tickerPlaying');
					table.removeClass('tickerGamePending');
					table.addClass('tickerGameFinished');
					break;
				default:
					table.removeClass('tickerGameFinished');
					table.removeClass('tickerGamePending');
					table.addClass('tickerPlaying');
					break;
			}

			var visitorwin = false;
			var homewin = false;
			if ((quarter === 'F') || (quarter === 'FO')) {
				var vs = game.get_visitorScore() * 1;
				var hs = game.get_homeScore() * 1;
				if (hs > vs) {
					homewin = true;
				} else if (vs > hs) {
					visitorwin = true;
				}
			}

			if (visitorwin) {
				this.__visitorCell.addClass('tickerTeamWinner');
				this.__visitorScoreCell.addClass('tickerTeamWinner');
			} else {
				this.__visitorCell.removeClass('tickerTeamWinner');
				this.__visitorScoreCell.removeClass('tickerTeamWinner');
			}

			if (homewin) {
				this.__homeCell.addClass('tickerTeamWinner');
				this.__homeScoreCell.addClass('tickerTeamWinner');
			} else {
				this.__homeCell.removeClass('tickerTeamWinner');
				this.__homeScoreCell.removeClass('tickerTeamWinner');
			}
		}

	};

	return GameTile;
});
