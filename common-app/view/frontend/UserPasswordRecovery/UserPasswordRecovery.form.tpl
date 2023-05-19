<div q-namespace="Omi\View" class="min-h-screen bg-main-accent-color flex items-center justify-center p-6">
	<div class="w-full relative">
		<div class="sm:mx-auto sm:w-full sm:max-w-md absolute top-0 left-0 right-0 -mt-24">
			<img src="<?= Q_APP_REL . 'code/res/main/images/logo.png' ?>" class="h-16 mx-auto" />

			<h2 class="text-center text-xl leading-9 font-extrabold text-white">
			  {{_T(338, 'Recover your password')}}
			</h2>
			<!-- <p class="mt-2 text-center text-sm leading-5 text-gray-600 max-w">
				Or
				<a href="#" class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition ease-in-out duration-150">
				  start your 14-day free trial
				</a>
			</p> -->
		</div>
		
		<div class="mt-6 sm:mx-auto sm:w-full sm:max-w-lg">
			<div class="bg-white py-6 px-4 shadow sm:rounded-lg sm:px-10">
				<form method="POST" autocomplete="off">
					<input type="hidden" name="__submitted" value="1" />

					<div class="login-wrapper">
						@php $err_msg = null;
						@if ($this->error)
							@if (is_string($this->error))
								@php $err_msg = $this->error;
							@elseif ((int)$this->error === \Omi\User::RECOVER_PASSWORD_USER_NOT_FOUND)
								@php $err_msg = "User with this combination was not found!";
							@elseif ($this->rcv_email_was_sent === false)
								@php $err_msg = "Unable to send recovery email!";
							@else
								@php $err_msg = "There was an error! Please try again later!";
							@endif
						@endif
						
						@if ($err_msg)
							<div class="text-red-500 text-sm font-medium"><?= $err_msg ?></div>
						@endif
						<?php
							if (!$this->mail_sent) : ?>

								@if ((!defined('Q_HIDE_USERNAME_IN_PASS_RECOVERY')) || !Q_HIDE_USERNAME_IN_PASS_RECOVERY)
									<div>
										<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('5a2a66622a128', 'Username') ?></label>
										<div class="mt-1 relative rounded-md shadow-sm">
											<input type="text" name="username" value="" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
										</div>
									</div>
								@endif

								<div class="mt-6">
									<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('5a2a666a192c4', 'Email') ?></label>
									<input placeholder="emaildomain.com" type="text" name="email" value="" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
								</div>


								<button class="mt-6 w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out" type="submit"><?= _T('5a2a665480556', 'Recover') ?></button>
								<a class="mt-6 w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md border border-gray-300 text-blue-600 bg-white hover:bg-white focus:outline-none focus:border-white focus:shadow-outline-white active:bg-white transition duration-150 ease-in-out" href="<?= qUrl("login") ?>"><?= _T('5a2a664c55a5e', 'Back to Login') ?></a>
						<?php else : ?>
							<div class="mb-2">{{_T(361, 'Confirm your password change by accessing your email address.')}}</div>
							<div>{{_T(362, 'Our team sent an email to')}} <strong>{{$_POST['email']}}</strong>.</div>
						<?php endif; ?>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
