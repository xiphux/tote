define(['jquery', './scoretickerengine', './scoretickerstrip', 'cookies'], function($, ScoreTickerEngine, ScoreTickerStrip) {
	
	function ScoreTicker(element)
	{
		this.__boundElement = $(element);
	}

	ScoreTicker.prototype = {
		
		__boundElement: null,
		__toggleDiv: null,
		__toggleLink: null,
		__contentDiv: null,
		__titleDiv: null,
		
		__engine: null,

		__strip: null,

		__hidden: false,

		initialize: function()
		{
			if ($.cookies.test()) {
				var ck = $.cookies.get('ToteScoretickerHidden');
				if (ck !== null) {
					this.__hidden = ck;
				}
			}

			this.__initElements();

			this.__engine = new ScoreTickerEngine();
			this.__engine.addObserver(this);
			this.__engine.initialize();

			this.__strip = new ScoreTickerStrip(this.__engine);
			this.__strip.addObserver(this);
			this.__strip.initialize();

			this.__contentDiv.append(this.__strip.get_element());

			if (!this.__hidden) {
				this.__engine.start();
			}
		},

		__initElements: function()
		{
			var toggleDiv = $(document.createElement('div'));
			toggleDiv.addClass('tickerToggleDiv');
			toggleDiv.addClass('subSection');
			this.__toggleDiv = toggleDiv;

			var toggleLink = $(document.createElement('a'));
			toggleLink.addClass('tickerToggleLink');

			if (this.__hidden) {
				toggleLink.text('Score ticker...');
				toggleLink.addClass('tickerClosed');
			} else {
				toggleLink.text('Score ticker');
				toggleLink.addClass('tickerOpen');
			}

			toggleLink.attr('href', '#');

			if (this.__hidden) {
				toggleLink.toggle($.proxy(function(event) {
					this.show();
					return false;
				}, this), $.proxy(function(event) {
					this.hide();
					return false;
				}, this));
			} else {
				toggleLink.toggle($.proxy(function(event) {
					this.hide();
					return false;
				}, this), $.proxy(function(event) {
					this.show();
					return false;
				}, this));
			}

			this.__toggleLink = toggleLink;

			toggleDiv.append(toggleLink);

			var contentDiv = $(document.createElement('div'));
			if (this.__hidden) {
				contentDiv.hide();
			}
			this.__contentDiv = contentDiv;
			toggleDiv.append(contentDiv);

			var titleDiv = $(document.createElement('div'));
			titleDiv.addClass('tickerTitle');
			this.__titleDiv = titleDiv;

			contentDiv.append(titleDiv);

			if (this.__hidden) {
				this.__boundElement.addClass('rounded-bottom');
				this.__boundElement.width(900);
			}
			this.__boundElement.append(toggleDiv);
		},

		show: function()
		{
			if (!this.__hidden)
				return;

			this.__toggleLink.text('Score ticker...');
			this.__toggleLink.removeClass('tickerClosed');
			this.__toggleLink.addClass('tickerOpen');

			this.__boundElement.removeClass('rounded-bottom');

			this.__contentDiv.show('fast', $.proxy(function() {
				this.__engine.start();
			}, this));

			if ($.cookies.test()) {
				var exp = new Date();
				exp.setDate(exp.getDate() + 365);
				$.cookies.set('ToteScoretickerHidden', false, {expiresAt: exp});
			}
			this.__hidden = false;
		},

		hide: function()
		{
			if (this.__hidden)
				return;

			this.__engine.stop();

			this.__contentDiv.hide('fast');

			this.__boundElement.addClass('rounded-bottom');

			this.__toggleLink.text('Score ticker...');
			this.__toggleLink.removeClass('tickerOpen');
			this.__toggleLink.addClass('tickerClosed');

			if ($.cookies.test()) {
				var exp = new Date();
				exp.setDate(exp.getDate() + 365);
				$.cookies.set('ToteScoretickerHidden', true, {expiresAt: exp});
			}
			this.__hidden = true;
		},

		observeChange: function(object, changeType, changeData)
		{
			if (object === this.__engine) {
				if (changeType !== 'propertychanged')
					return;

				for (var prop in changeData) {
					if (!changeData.hasOwnProperty(prop))
						continue;

					switch (prop) {
						case 'weekString':
							this.__titleDiv.text(changeData.weekString);
							break;
					}
				}
			} else if (object === this.__strip) {
				switch (changeType) {
					case 'widthchanged':
						if (!this.__hidden) {
							this.__boundElement.width(changeData + 4);
						}
						break;
				}
			}
		}

	};

	return ScoreTicker;

});
