var oldPassword = "";

function initGeneratePassword()
{
	$('#generateButton').click(function() {
		var letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		var numbers = '0123456789';

		var pass = "";

		var rlet = Math.floor(Math.random() * letters.length);
		pass += letters.substring(rlet, rlet+1);

		for (var i = 0; i < 5; i++) {
			var rnum = Math.floor(Math.random() * numbers.length);
			pass += numbers.substring(rnum, rnum+1);
		}

		$('#password').val(pass);
		$('#password2').val(pass);
		$('#randomPasswordDisplay').text('Generated: ' + pass);
		oldPassword = pass;
	});

	$('#password').keyup(function() {
		var pVal = $('#password').val();
		if (pVal != oldPassword) {
			oldPassword = pVal;
			$('#randomPasswordDisplay').text('');
		}
	});
}

$(document).ready(function() {
	initGeneratePassword();
});
