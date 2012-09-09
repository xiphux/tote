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
		__titleSpan: null,
		__loaderImage: null,
		
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

			var engine = new ScoreTickerEngine();
			engine.addObserver(this);
			this.__engine = engine;

			var strip = new ScoreTickerStrip(engine);
			strip.addObserver(this);
			strip.initialize();
			this.__strip = strip;

			var stripelement = strip.get_element();
			this.__contentDiv.append(stripelement);

			if (!this.__hidden) {
				engine.start();
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

			var titleSpan = $(document.createElement('span'));
			titleSpan.text('Loading...');
			this.__titleSpan = titleSpan;

			titleDiv.append(titleSpan);

			var loaderImage = $(document.createElement('img'));
			loaderImage.attr('src', 'images/scoreticker-loader.gif');
			loaderImage.css('margin-left', '10px');
			loaderImage.css('display', 'inline-block');
			this.__loaderImage = loaderImage;

			titleDiv.append(loaderImage);

			contentDiv.append(titleDiv);

			var boundElement = this.__boundElement;
			if (this.__hidden) {
				boundElement.addClass('rounded-bottom');
				boundElement.width(900);
			}
			boundElement.append(toggleDiv);
		},

		show: function()
		{
			if (!this.__hidden)
				return;

			var toggleLink = this.__toggleLink;
			toggleLink.text('Score ticker...');
			toggleLink.removeClass('tickerClosed');
			toggleLink.addClass('tickerOpen');

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

			var toggleLink = this.__toggleLink;
			toggleLink.text('Score ticker...');
			toggleLink.removeClass('tickerOpen');
			toggleLink.addClass('tickerClosed');

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
				switch (changeType) {
					case 'propertychanged':
						for (var prop in changeData) {
							if (!changeData.hasOwnProperty(prop))
								continue;

							switch (prop) {
								case 'weekString':
									this.__titleSpan.text(changeData.weekString);
									break;
							}
						}
						break;
					case 'datarequested':
						this.__loaderImage.fadeTo('slow', 1);
						break;
					case 'datareceived':
						this.__loaderImage.fadeTo('slow', 0);
						break;
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
