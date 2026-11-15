$(function () {
	$('#formLogin').on('submit', function (e) {
		e.preventDefault();
		const form = this;
		$(form).addClass('was-validated');
		if (!form.checkValidity()) return;

		const $btn = $(form).find('button[type=submit]').prop('disabled', true);
		$('#loginError').addClass('d-none');

		App.api('auth/login', {
			email: $('#email').val(),
			password: $('#password').val()
		})
			.done(() => window.location.href = server_url + 'dashboard')
			.fail(msg => {
				$('#loginError').text(msg).removeClass('d-none');
				$('#password').val('').focus();
				$(form).removeClass('was-validated');
			})
			.always(() => $btn.prop('disabled', false));
	});
});
