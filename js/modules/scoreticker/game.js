define(['module'], function(module) {
	
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

			this.__updates.year = year;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.seasonType = seasonType;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.week = week;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.gsis = gsis;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.eid = eid;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			var timezoneoffset = module.config().timezoneoffset;
			if (timezoneoffset) {
				var shifted = this.__shiftStart(day, time, timezoneoffset);
				this.__localStartDay = shifted.day;
				this.__localStartTime = shifted.time;
			} else {
				this.__localStartDay = day;
				this.__localStartTime = time;
			}

			this.__updates.startDay = day;
			this.__updates.startTime = time;
			if (!this.__delayNotify) {
				this.__notify();
			}
		},

		__shiftStart: function(day, time, offset) {
			var now = new Date();

			now.setHours(0, 0, 0, 0);

			var dayInt = this.__dayToNum(day);
			if (dayInt < 0) {
				return { day: day, time: time };
			}

			while (now.getDay() != dayInt) {
				now.setTime(now.getTime() + 86400000);
			}

			var timepieces = time.split(':');
			timepieces[0] = parseInt(timepieces[0]);
			timepieces[1] = parseInt(timepieces[1]);
			if (timepieces[0] < 11)
				timepieces[0] += 12;
			now.setHours(parseInt(timepieces[0]), parseInt(timepieces[1]));

			now.setTime(now.getTime() + (offset * 1000));

			var shiftedDay = this.__numToDay(now.getDay());
			var shiftedHours = now.getHours();
			if (shiftedHours > 12)
				shiftedHours -= 12;
			var shiftedMin = now.getMinutes();
			if (shiftedMin < 10)
				shiftedMin = "0" + shiftedMin;
			var shiftedTime = shiftedHours + ":" + shiftedMin;

			return { day: shiftedDay, time: shiftedTime };
		},

		__dayToNum: function(day)
		{
			day = day.toUpperCase();
			switch (day) {
				case 'SUN':
					return 0;
				case 'MON':
					return 1;
				case 'TUE':
					return 2;
				case 'WED':
					return 3;
				case 'THU':
					return 4;
				case 'FRI':
					return 5;
				case 'SAT':
					return 6;
			}

			return -1;
		},

		__numToDay: function(num)
		{
			switch (num) {
				case 0:
					return 'Sun';
				case 1:
					return 'Mon';
				case 2:
					return 'Tue';
				case 3:
					return 'Wed';
				case 4:
					return 'Thu';
				case 5:
					return 'Fri';
				case 6:
					return 'Sat';
			}

			return '';
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

			this.__updates.visitor = visitor;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.visitorNickname = visitorNickname;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.home = home;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.homeNickname = homeNickname;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.homeScore = homeScore;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.visitorScore = visitorScore;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.redZone = redZone;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.quarter = quarter;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.clock = clock;
			if (!this.__delayNotify) {
				this.__notify();
			}
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

			this.__updates.possession = possession;
			if (!this.__delayNotify) {
				this.__notify();
			}
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
