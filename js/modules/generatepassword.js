define(function() {
	return function() {
		var letters = 'ABCDEFGHJKMNPQRSTUVWXYZ';
		var numbers = '23456789';

		var pass = "";

		var rlet = Math.floor(Math.random() * letters.length);
		pass += letters.substring(rlet, rlet+1);

		for (var i = 0; i < 5; i++) {
			var rnum = Math.floor(Math.random() * numbers.length);
			pass += numbers.substring(rnum, rnum+1);
		}

		return pass;
	}
});
