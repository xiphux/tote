define(function() {

	function BigPlay() {
		this.__observers = [];
		this.__updates = {};
	}

	BigPlay.prototype = {

		__eid: null,
		__gsis: null,
		__id: null,
		__team: null,
		__message: null,

		__observers: null,
		__updates: null,
		__delayNotify: false,

		get_eid: function()
		{
			return this.__eid;
		},

		set_eid: function(eid)
		{
			if (eid === this.__eid)
				return;

			this.__eid = eid;

			this.__queueUpdates({ eid: eid });
		},

		get_gsis: function()
		{
			return this.__gsis;
		},

		set_gsis: function(gsis)
		{
			if (gsis === this.__gsis)
				return;

			this.__gsis = gsis;

			this.__queueUpdates({ gsis: gsis });
		},

		get_id: function()
		{
			return this.__id;
		},

		set_id: function(id)
		{
			if (id === this.__id)
				return;

			this.__id = id;

			this.__queueUpdates({ id: id });
		},

		get_team: function()
		{
			return this.__team;
		},

		set_team: function(team)
		{
			if (team === this.__team)
				return;

			this.__team = team;

			this.__queueUpdates({ team: team });
		},

		get_message: function()
		{
			return this.__message;
		},

		set_message: function(message)
		{
			if (message === this.__message)
				return;

			this.__message = message;

			this.__queueUpdates({ message: message });
		},

		set_data: function(data)
		{
			if (!data)
				return;

			this.__delayNotify = true;
			for (var prop in data) {
				if (!data.hasOwnProperty(prop))
					continue;

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

			this.__delayNotify = true;
			this.__notify();
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

		__queueUpdates: function(updates)
		{
			if (!updates)
				return;

			var modified = false;

			var mainUpdates = this.__updates;
			for (var key in updates) {
				if (updates.hasOwnProperty(key)) {
					mainUpdates[key] = updates[key];
					modified = true;
				}
			}

			if (modified && !this.__delayNotify) {
				this.__notify();
			}
		},

		__notify: function()
		{
			var observers = this.__observers;

			if (observers.length === 0)
				return;

			var modified = false;
			var updates = this.__updates;
			for (var key in updates) {
				if (updates.hasOwnProperty(key)) {
					modified = true;
					break;
				}
			}
			if (!modified)
				return;

			var observer = null;
			for (var i = 0; i < observers.length; i++) {
				observer = observers[i];
				if (typeof observer.observeChange === 'function') {
					observer.observeChange(this, 'propertychanged', updates);
				}
			}

			this.__updates = {};
		}

	};

	return BigPlay;

});
