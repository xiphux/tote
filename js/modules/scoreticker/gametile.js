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

		initialize: function()
		{
			var a = $(document.createElement('a'));
			this.__base = a;

			var table = $(document.createElement('table'));
			this.__table = table;
			table.addClass('tickerGameTile');
			if (this.__game && this.__game.get_redZone()) {
				table.addClass('tickerGameRedZone');
			}

			var visitorRow = $(document.createElement('tr'));

			var visitorCell = $(document.createElement('td'));
			this.__visitorCell = visitorCell;
			visitorCell.addClass('tickerGameTeam');
			if (this.__game) {
				visitorCell.text(this.__game.get_visitor());
			}
			visitorRow.append(visitorCell);

			var visitorPossessionCell = $(document.createElement('td'));
			this.__visitorPossessionCell = visitorPossessionCell;
			visitorPossessionCell.addClass('tickerPossession');
			if (this.__game) {
				if (this.__game.playing() && (this.__game.get_possession() === this.__game.get_visitor())) {
					visitorPossessionCell.text('<');
				}
			}
			visitorRow.append(visitorPossessionCell);

			var visitorScoreCell = $(document.createElement('td'));
			this.__visitorScoreCell = visitorScoreCell;
			visitorScoreCell.addClass('tickerGameScore');
			if (this.__game && (this.__game.get_quarter() !== 'P')) {
				visitorScoreCell.text(this.__game.get_visitorScore());
			}
			visitorRow.append(visitorScoreCell);

			table.append(visitorRow);


			var homeRow = $(document.createElement('tr'));

			var homeCell = $(document.createElement('td'));
			this.__homeCell = homeCell;
			homeCell.addClass('tickerGameTeam');
			if (this.__game) {
				homeCell.text(this.__game.get_home());
			}
			homeRow.append(homeCell);

			var homePossessionCell = $(document.createElement('td'));
			this.__homePossessionCell = homePossessionCell;
			homePossessionCell.addClass('tickerPossession');
			if (this.__game) {
				if (this.__game.playing() && (this.__game.get_possession() === this.__game.get_home())) {
					homePossessionCell.text('<');
				}
			}
			homeRow.append(homePossessionCell);

			var homeScoreCell = $(document.createElement('td'));
			this.__homeScoreCell = homeScoreCell;
			homeScoreCell.addClass('tickerGameScore');
			if (this.__game && (this.__game.get_quarter() !== 'P')) {
				homeScoreCell.text(this.__game.get_homeScore());
			}
			homeRow.append(homeScoreCell);

			table.append(homeRow);

			var statusRow = $(document.createElement('tr'));

			var statusCell = $(document.createElement('td'));
			this.__statusCell = statusCell;
			statusCell.addClass('tickerGameStatus');
			statusCell.attr('colspan', '3');
			if (this.__game) {
				statusCell.text(this.__game.get_status());
			}
			statusRow.append(statusCell);

			table.append(statusRow);

			if (this.__game) {
				a.attr('href', this.__game.get_url());
				a.attr('id', this.__game.get_gsis());
			}
			a.attr('target', '_blank');
			a.append(table);

			this.__updateState();
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
						this.__homeScoreCell.text(changeData.homeScore);
						break;
					case 'visitorScore':
						this.__visitorScoreCell.text(changeData.visitorScore);
						break;
					case 'redZone':
						if (changeData.redZone) {
							this.__table.addClass('tickerGameRedZone');
						} else {
							this.__table.removeClass('tickerGameRedZone');
						}
						break;
					case 'quarter':
						this.__updateState();
						break;
					case 'possession':
						if (changeData.possession === this.__game.get_visitor()) {
							this.__visitorPossessionCell.text('<');
						} else {
							this.__visitorPossessionCell.text('');
						}
						if (changeData.possession === this.__game.get_home()) {
							this.__homePossessionCell.text('<');
						} else {
							this.__homePossessionCell.text('');
						}
						break;
				}
			}
		},

		__updateState: function()
		{
			if (!this.__game)
				return;

			var table = this.__table;
			var quarter = this.__game.get_quarter();
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
				var vs = this.__game.get_visitorScore() * 1;
				var hs = this.__game.get_homeScore() * 1;
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
