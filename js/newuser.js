define(['jquery', 'modules/generatepassword', 'common'], function($, generatepassword) {
	$('#generateButton').click(function() {
		var pass = generatepassword();

		$('#password').val(pass).data('oldVal', pass);
		$('#password2').val(pass).data('oldVal', pass);
		$('#randomPasswordDisplay').text('Generated: ' + pass);
	});
	$('#password, #password2').keyup(function() {
		var jThis = $(this);
		if (jThis.val() != jThis.data('oldVal')) {
			jThis.data('oldVal', jThis.val());
			$('#randomPasswordDisplay').text('');
		}
	});

	$('#password').data('oldVal', $('#password').val());
	$('#password2').data('oldVal', $('#password2').val());
});
