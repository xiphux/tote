define(['jquery', './gametile', './bigplaypopup', './bigplayqueue'], function($, GameTile, BigPlayPopup, BigPlayQueue) {

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
			this.__bigPlayQueue = new BigPlayQueue(this.__container);
			if (this.__engine.started()) {
				this.__bigPlayQueue.start();
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
			if (this.__gameTiles[gsis]) {
				this.__gameTiles[gsis].get_element().parent().remove();
				delete this.__gameTiles[gsis];
			}
		},

		__addBigPlayPopup: function(bigPlay)
		{
			if (!bigPlay)
				return;

			var count = 0;
			var gsis = null;
			for (gsis in this.__gameTiles) {
				if (this.__gameTiles.hasOwnProperty(gsis)) {
					count++;
				}
			}
			var half = Math.ceil(count/2);

			var idx = 0;
			for (var gsis in this.__gameTiles) {
				if (this.__gameTiles.hasOwnProperty(gsis)) {
					if (this.__gameTiles[gsis]) {
						idx++;
						if (gsis === bigPlay.get_gsis()) {
							var popup = new BigPlayPopup(bigPlay, this.__gameTiles[gsis], (idx >= half));
							this.__bigPlayQueue.push(popup);
							return;
						}
					}
				}
			}
		},

		get_element: function()
		{
			return this.__container;
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
						for (i = 0; i < changeData.addedGames.length; i++) {
							this.__addGameTile(changeData.addedGames[i]);
						}
						this.__notify('widthchanged');
						break;
					case 'removedGames':
						for (i = 0; i < changeData.removedGames.length; i++) {
							this.__removeGameTile(changeData.removedGames[i]);
						}
						this.__notify('widthchanged');
						break;
					case 'addedBigPlays':
						for (i = 0; i < changeData.addedBigPlays.length; i++) {
							this.__addBigPlayPopup(changeData.addedBigPlays[i]);
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

		__notify: function(changeType)
		{
			if (this.__observers.length === 0)
				return;

			var changeData = null;
			if (changeType == 'widthchanged') {
				changeData = this.__gameTable.width();
			}

			var observer = null;
			for (var i = 0; i < this.__observers.length; i++) {
				observer = this.__observers[i];
				if (observer.observeChange) {
					this.__observers[i].observeChange(this, changeType, changeData);
				}
			}
		}
	};

	return ScoreTickerStrip;

});
