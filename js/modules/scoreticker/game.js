define(['modules/scoreticker/localstart'], function(localStart) {
	
	function Game() {
		this.__observers = [];
		this.__updates = {};
	}

	Game.prototype = {

		__year: null,
		__seasonType: null,
		__week: null,

		__gsis: null,
		__eid: null,

		__startDay: null,
		__startTime: null,
		__localStartDay: null,
		__localStartTime: null,

		__vistor: null,
		__visitorNickname: null,
		__home: null,
		__homeNickname: null,

		__homeScore: null,
		__visitorScore: null,

		__redZone: false,
		__quarter: null,
		__clock: null,
		__possession: null,

		__observers: null,
		__updates: null,
		__delayNotify: false,

		get_year: function()
		{
			return this.__year;
		},

		set_year: function(year)
		{
			if (year === this.__year)
				return;

			this.__year = year;

			this.__queueUpdates({ year: year });
		},

		get_seasonType: function()
		{
			return this.__seasonType;
		},

		set_seasonType: function(seasonType)
		{
			if (seasonType === this.__seasonType)
				return;

			this.__seasonType = seasonType;

			this.__queueUpdates({ seasonType: seasonType });
		},

		get_week: function()
		{
			return this.__week;
		},

		set_week: function(week)
		{
			if (week === this.__week)
				return;

			this.__week = week;

			this.__queueUpdates({ week: week });
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

		get_start: function()
		{
			return { day: this.__startDay, time: this.__startTime };
		},

		set_start: function(day, time)
		{
			if ((this.__startDay === day) && (this.__startTime === time))
				return;

			this.__startDay = day;
			this.__startTime = time;

			var shifted = localStart(day, time);
			this.__localStartDay = shifted.day;
			this.__localStartTime = shifted.time;

			this.__queueUpdates({ startDay: day, startTime: time });
		},

		__shiftStart: function(day, time, offset) {
		},

		__dayToNum: function(day)
		{
		},

		__numToDay: function(num)
		{
		},

		get_visitor: function()
		{
			return this.__visitor;
		},

		set_visitor: function(visitor)
		{
			if (visitor === this.__visitor)
				return;

			this.__visitor = visitor;

			this.__queueUpdates({ visitor: visitor });
		},

		get_visitorNickname: function()
		{
			return this.__visitorNickname;
		},

		set_visitorNickname: function(visitorNickname)
		{
			if (visitorNickname === this.__visitorNickname)
				return;

			this.__visitorNickname = visitorNickname;

			this.__queueUpdates({ visitorNickname: visitorNickname });
		},

		get_home: function()
		{
			return this.__home;
		},

		set_home: function(home)
		{
			if (home === this.__home)
				return;

			this.__home = home;

			this.__queueUpdates({ home: home });
		},

		get_homeNickname: function()
		{
			return this.__homeNickname;
		},

		set_homeNickname: function(homeNickname)
		{
			if (homeNickname === this.__homeNickname)
				return;

			this.__homeNickname = homeNickname;

			this.__queueUpdates({ homeNickname: homeNickname });
		},

		get_homeScore: function()
		{
			return this.__homeScore;
		},

		set_homeScore: function(homeScore)
		{
			if (homeScore === this.__homeScore)
				return;

			this.__homeScore = homeScore;

			this.__queueUpdates({ homeScore: homeScore });
		},

		get_visitorScore: function()
		{
			return this.__visitorScore;
		},

		set_visitorScore: function(visitorScore)
		{
			if (visitorScore === this.__visitorScore)
				return;

			this.__visitorScore = visitorScore;

			this.__queueUpdates({ visitorScore: visitorScore });
		},

		get_redZone: function()
		{
			return this.__redZone;
		},

		set_redZone: function(redZone)
		{
			if (redZone === this.__redZone)
				return;

			this.__redZone = redZone;

			this.__queueUpdates({ redZone: redZone });
		},

		get_quarter: function()
		{
			return this.__quarter;
		},

		set_quarter: function(quarter)
		{
			if (quarter === this.__quarter)
				return;

			this.__quarter = quarter;

			this.__queueUpdates({ quarter: quarter });
		},

		get_clock: function()
		{
			return this.__clock;
		},

		set_clock: function(clock)
		{
			if (clock === this.__clock)
				return;

			this.__clock = clock;

			this.__queueUpdates({ clock: clock });
		},

		get_possession: function()
		{
			return this.__possession;
		},

		set_possession: function(possession)
		{
			if (possession === this.__possession)
				return;

			this.__possession = possession;

			this.__queueUpdates({ possession: possession });
		},

		set_data: function(data)
		{
			if (!data)
				return;

			this.__delayNotify = true;

			for (var prop in data) {
				if (data.hasOwnProperty(prop)) {
					switch (prop) {

						case 'year':
							this.set_year(data.year);
							break;

						case 'seasonType':
							this.set_seasonType(data.seasonType);
							break;

						case 'week':
							this.set_week(data.week);
							break;

						case 'gsis':
							this.set_gsis(data.gsis);
							break;

						case 'eid':
							this.set_eid(data.eid);
							break;

						case 'start':
							if (data.start.hasOwnProperty('day') && data.start.hasOwnProperty('time')) {
								this.set_start(data.start.day, data.start.time);
							}
							break;

						case 'visitor':
							this.set_visitor(data.visitor);
							break;

						case 'visitorNickname':
							this.set_visitorNickname(data.visitorNickname);
							break;

						case 'home':
							this.set_home(data.home);
							break;

						case 'homeNickname':
							this.set_homeNickname(data.homeNickname);
							break;

						case 'homeScore':
							this.set_homeScore(data.homeScore);
							break;

						case 'visitorScore':
							this.set_visitorScore(data.visitorScore);
							break;

						case 'redZone':
							this.set_redZone(data.redZone);
							break;

						case 'quarter':
							this.set_quarter(data.quarter);
							break;

						case 'clock':
							this.set_clock(data.clock);
							break;

						case 'possession':
							this.set_possession(data.possession);
							break;
					}
				}
			}

			this.__delayNotify = false;
			this.__notify();
		},

		playing: function()
		{
			var q = this.__quarter;
			if ((q !== null) && (q !== 'P') && (q !== 'F') && (q !== 'FO') && (q !== 'H')) {
				return true;
			}
			return false;
		},

		active: function()
		{
			return (this.playing() || (this.__quarter === 'H'));
		},

		get_url: function()
		{
			var typestr;
			if (this.__seasonType == 'P') {
				typestr = 'PRE';
			} else if (this.__seasonType == 'R') {
				typestr = 'REG';
			} else {
				return '';
			}

			return 'http://www.nfl.com/gamecenter/' + this.__eid + '/' + this.__year + '/' + typestr + this.__week + '/' + this.__visitorNickname + '@' + this.__homeNickname;
		},

		get_status: function()
		{
			switch (this.__quarter) {

				case 'P':
					// pending game
					return this.__localStartDay + ' ' + this.__localStartTime;

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
					return 'Q' + this.__quarter + ' ' + this.__clock;

				default:
					// quarter other than regulation (overtime)
					return 'OT ' + this.__clock;
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

		__queueUpdates: function(updates)
		{
			if (!updates)
				return;

			var modified = false;

			for (var key in updates) {
				if (updates.hasOwnProperty(key)) {
					this.__updates[key] = updates[key];
					modified = true;
				}
			}

			if (modified && !this.__delayNotify) {
				this.__notify();
			}
		},

		__notify: function()
		{
			if (this.__observers.length === 0)
				return;

			var modified = false;
			for (var key in this.__updates) {
				if (this.__updates.hasOwnProperty(key)) {
					modified = true;
					break;
				}
			}
			if (!modified)
				return;

			if (this.__updates.hasOwnProperty('eid') || this.__updates.hasOwnProperty('year') || this.__updates.hasOwnProperty('seasonType') || this.__updates.hasOwnProperty('week') || this.__updates.hasOwnProperty('visitorNickname') || this.__updates.hasOwnProperty('homeNickname')) {
				this.__updates.url = this.get_url();
			}

			if (this.__updates.hasOwnProperty('quarter') || this.__updates.hasOwnProperty('startDay') || this.__updates.hasOwnProperty('startTime') || this.__updates.hasOwnProperty('clock')) {
				this.__updates.status = this.get_status();
			}

			var observer = null;
			for (var i = 0; i < this.__observers.length; i++) {
				observer = this.__observers[i];
				if (observer.observeChange) {
					this.__observers[i].observeChange(this, 'propertychanged', this.__updates);
				}
			}

			this.__updates = {};
		}

	};

	return Game;

});
