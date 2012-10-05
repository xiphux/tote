define(['jquery', 'cs!./gametile', './bigplaypopup', './bigplayqueue'], function($, GameTile, BigPlayPopup, BigPlayQueue) {

	function ScoreTickerStrip(engine)
	{
		if (engine) {
			this.__engine = engine;
			engine.addObserver(this);
		}

		this.__gameTiles = {};

		this.__observers = [];
	}

	ScoreTickerStrip.prototype = {
		
		__container: null,
		__gameTable: null,
		__gameRow: null,

		__gameTiles: null,

		__engine: null,

		__bigPlayQueue: null,

		__observers: null,

		initialize: function()
		{
			this.__initElements();
			var bigPlayQueue = new BigPlayQueue(this.__container);
			this.__bigPlayQueue = bigPlayQueue;
			if (this.__engine.started()) {
				bigPlayQueue.start();
			}
		},

		__initElements: function()
		{
			var container = $(document.createElement('div'));
			container.addClass('tickerContainerDiv');
			this.__container = container;

			var gameTable = $(document.createElement('table'));
			gameTable.addClass('tickerGameTable');
			this.__gameTable = gameTable;

			var gameRow = $(document.createElement('tr'));
			this.__gameRow = gameRow;

			gameTable.append(gameRow);
			container.append(gameTable);
		},

		__addGameTile: function(game)
		{
			if (!game)
				return;

			var gsis = game.get_gsis();

			var td = $(document.createElement('td'));
			td.addClass('tickerGameCell');
			
			var gameTile = new GameTile(game);
			gameTile.initialize();
			this.__gameTiles[gsis] = gameTile;

			td.append(gameTile.get_element());

			this.__gameRow.append(td);
		},

		__removeGameTile: function(game)
		{
			if (!game)
				return;

			var gsis = game.get_gsis();
			var gameTiles = this.__gameTiles;
			if (gameTiles[gsis]) {
				gameTiles[gsis].get_element().parent().remove();
				delete gameTiles[gsis];
			}
		},

		__addBigPlayPopup: function(bigPlay)
		{
			if (!bigPlay)
				return;

			var count = 0;
			var gameTiles = this.__gameTiles;
			var gsis = null;
			for (gsis in gameTiles) {
				if (gameTiles.hasOwnProperty(gsis)) {
					count++;
				}
			}
			var half = Math.ceil(count/2);

			var idx = 0;
			for (var gsis in gameTiles) {
				if (!gameTiles.hasOwnProperty(gsis))
					continue;

				if (!gameTiles[gsis])
					continue;

				idx++;
				if (gsis === bigPlay.get_gsis()) {
					var popup = new BigPlayPopup(bigPlay, gameTiles[gsis], (idx >= half));
					this.__bigPlayQueue.push(popup);
					return;
				}
			}
		},

		get_element: function()
		{
			return this.__container;
		},

		get_width: function()
		{
			return this.__gameTable.width();
		},

		observeChange: function(object, changeType, changeData)
		{
			if (object !== this.__engine)
				return;

			if (changeType !== 'propertychanged')
				return;

			var i;
			for (var prop in changeData) {
				if (!changeData.hasOwnProperty(prop))
					continue;

				switch (prop) {
					case 'addedGames':
						var addedGames = changeData.addedGames;
						for (i = 0; i < addedGames.length; i++) {
							this.__addGameTile(addedGames[i]);
						}
						this.__notify('widthchanged');
						break;
					case 'removedGames':
						var removedGames = changeData.removedGames;
						for (i = 0; i < removedGames.length; i++) {
							this.__removeGameTile(removedGames[i]);
						}
						this.__notify('widthchanged');
						break;
					case 'addedBigPlays':
						var addedBigPlays = changeData.addedBigPlays;
						for (i = 0; i < addedBigPlays.length; i++) {
							this.__addBigPlayPopup(addedBigPlays[i]);
						}
						break;
					case 'started':
						if (changeData.started) {
							this.__bigPlayQueue.start();
						} else {
							this.__bigPlayQueue.stop();
						}
						break;
				}
			}
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

		__notify: function(changeType)
		{
			var observers = this.__observers;

			if (observers.length === 0)
				return;

			var observer = null;
			for (var i = 0; i < observers.length; i++) {
				observer = observers[i];
				if (typeof observer.observeChange === 'function') {
					observer.observeChange(this, changeType);
				}
			}
		}
	};

	return ScoreTickerStrip;

});
