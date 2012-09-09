define(['jquery'], function($) {

	function BigPlayQueue(container) {
		this.__queue = [];
		this.__queued = [];
		this.__container = container;
	}

	BigPlayQueue.prototype = {
	
		__container: null,

		__queue: null,
		__queued: null,

		__activeEntry: null,
		__timer: null,

		__started: false,

		start: function()
		{
			if (this.__started)
				return;

			this.__started = true;

			this.__showNext();
		},

		stop: function()
		{
			if (!this.__started)
				return;

			this.__started = false;

			this.__hideCurrent();

			this.__queue = [];
		},

		started: function()
		{
			return this.__started;
		},

		push: function(popup)
		{
			if (!popup)
				return;

			var id = popup.get_bigPlay().get_id();
			if (this.__queued[id]) {
				return;
			}

			this.__queued[id] = true;

			this.__queue.push(popup);

			if (this.__started && !this.__activeEntry) {
				this.__showNext();
			}
		},

		__showNext: function()
		{
			if (this.__activeEntry)
				return;

			var queue = this.__queue;

			if (queue.length < 1)
				return;

			var activeEntry = queue[0];
			this.__activeEntry = activeEntry;
			queue.shift();

			activeEntry.initialize();
			this.__container.append(activeEntry.get_element());

			activeEntry.show($.proxy(function() {
				this.__timer = window.setTimeout($.proxy(function() {
					this.__hideCurrent();
				}, this), 10000);
			}, this));
		},

		__hideCurrent: function()
		{
			if (!this.__activeEntry)
				return;

			window.clearTimeout(this.__timer);
			this.__timer = null;

			this.__activeEntry.hide($.proxy(function() {

				this.__activeEntry.get_element().remove();
				this.__activeEntry = null;

				if ((this.__queue.length > 0) && this.__started) {
					this.__showNext();
				}

			}, this));
		}

	};

	return BigPlayQueue;

});
