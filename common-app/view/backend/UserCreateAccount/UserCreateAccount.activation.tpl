<div class="min-h-screen bg-main-accent-color flex items-center justify-center p-6">
	<div class="w-full relative">
		<div class="qc-grid-properties" data-properties="{{$this->getJsProperties()}}"></div>
		<div class="sm:mx-auto sm:w-full sm:max-w-md">
			<img src="<?= Q_APP_REL . 'code/res/main/images/logo.png' ?>" class="h-16 mx-auto" />

			<h2 class="text-center text-xl leading-9 font-extrabold text-white">
			  {{_T(204, 'Account Confimed')}}
			</h2>
		</div>
		
		<div class="mt-6 sm:mx-auto sm:w-full sm:max-w-4xl">
			<div class="bg-white py-6 px-4 shadow sm:rounded-lg sm:px-10">
				<p>{{_T(243, 'Hello')}},</p><br />
				<p>{{_T(205, 'Your account has been confirmed. Someone will activate your account soon.')}}</p><br />
				<p>{{_T(244, 'With love')}},</p>
				<p>{{_T(245, 'H2B team')}}</p>
			</div>
		</div>
	</div>
</div>
