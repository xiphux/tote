define(['module'], function(module) {

	function dayToNum(day) {
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
	}

	function numToDay(num) {
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
	}

	return function(day, time) {
		var offset = module.config().timezoneoffset;
		if (!offset) {
			return { day: day, time: time };
		}

		var now = new Date();

		now.setHours(0, 0, 0, 0);

		var dayInt = dayToNum(day);
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

		var shiftedDay = numToDay(now.getDay());
		var shiftedHours = now.getHours();
		if (shiftedHours > 12)
			shiftedHours -= 12;
		var shiftedMin = now.getMinutes();
		if (shiftedMin < 10)
			shiftedMin = "0" + shiftedMin;
		var shiftedTime = shiftedHours + ":" + shiftedMin;

		return { day: shiftedDay, time: shiftedTime };
	}

});
