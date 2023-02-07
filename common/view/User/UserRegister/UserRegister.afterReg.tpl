<div class='after-register' q-args="$register_result = null, $form_data = null">
	<div class='container'>
		<div class='row if-not-60-fill-all bg-white card-shadow p-all-10 m-bottom-20'>
			@var $title = _TEXT('after_register_title');
			@if ($title)
				<h1><?= $title ?></h1>
			@endif
			@if ($register_result)
				<div class='register-success'><?= _TEXT('after_register_success')?></div>
			@else
				<div class='register-error'><?= _TEXT('after_register_error') ?></div>
			@endif
		</div>
	</div>
</div>