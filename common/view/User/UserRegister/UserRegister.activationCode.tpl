<div class='register-confirmed' q-args="$activation_code = null, $confirm_result = null">
	<div class='container'>
		<div class='row if-not-60-fill-all bg-white card-shadow p-all-10 m-bottom-20'>
		@var $title = _TEXT('register_confirmed_title');
		@if ($title)
			<h1><?= $title ?></h1>
		@endif
		@if ($confirm_result)
			<div class='register-confirm-success'><?= _TEXT('register_confirmed_success') ?></div>
		@else
			<div class='register-confirm-error'><?= _TEXT('register_confirmed_error') ?></div>
		@endif
		</div>
	</div>
</div>
