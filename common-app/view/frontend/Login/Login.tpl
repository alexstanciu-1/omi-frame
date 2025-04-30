<div q-namespace="Omi\View" class="min-h-screen p-6">
	@if (defined('Q_USER_LANGUAGE') && Q_USER_LANGUAGE)
		<div class="flex items-center md:ml-6 justify-end">
			<div class="ml-3 relative mr-3">
				<div class="flex items-center">
					<button class="border max-w-xs flex items-center text-sm rounded-full focus:outline-none focus:bg-cool-gray-100 lg:p-2 lg:rounded-md lg:hover:bg-cool-gray-100 js-language-dd-trigger bg-white">
						<p class="whitespace-nowrap ml-3 text-cool-gray-700 text-sm leading-5 font-medium"><?= Q_USER_LANGUAGE ?></p>

						<svg class="flex-shrink-0 ml-1 h-6 w-6 text-cool-gray-400" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
						</svg>
					</button>
				</div>
				
				<div x-description="Language dropdown panel, show/hide based on dropdown state." x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-24 rounded-md shadow-lg js-language-dd hidden z-10">
					<div class="rounded-md bg-white shadow-xs" role="menu" aria-orientation="vertical">
						<a href="<?= Q_REQUEST_BASE ?>login" class="block px-4 py-2 text-sm text-cool-gray-700 hover:bg-cool-gray-100 transition ease-in-out duration-150 <?= (Q_USER_LANGUAGE == 'RO') ? 'font-bold' : '' ?>" role="menuitem">RO</a>
						<a href="<?= Q_REQUEST_BASE . 'en/login/' ?>" class="block px-4 py-2 text-sm text-cool-gray-700 hover:bg-cool-gray-100 transition ease-in-out duration-150 <?= (Q_USER_LANGUAGE == 'EN') ? 'font-bold' : '' ?>" role="menuitem">EN</a>
					</div>
				</div>
			</div>
		</div>
	@endif
	
	<div class="min-h-screen flex justify-center items-center p-6 relative">
		<div class="w-full relative">
			<div class="sm:mx-auto sm:w-full sm:max-w-md absolute left-0 right-0 top-0 -mt-24">
				<img src="<?= Q_APP_REL . 'code/res/main/images/logo.png' ?>" class="h-16 mx-auto" />

				<h2 class="text-center text-xl leading-9 font-extrabold text-white">{{_T(318, 'Sign in to your account')}}</h2>
			</div>

			<div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
				<div class="bg-white py-6 px-4 shadow sm:rounded-lg sm:px-10">
					<form method="POST" autocomplete="off">
						<input type="hidden" name="__submitted" value="1" />

						<?php 
							$err_msg = null;
							if ($this->error)
							{
								if (is_string($this->error))
									$err_msg = $this->error;
								else if ($this->error == \Omi\User::LOGIN_BANNED)
									$err_msg = "Your ip or your username was banned for too many tries!";
								else if (($this->error == \Omi\User::LOGIN_INVALID_USER_OR_PASSWORD) || ($this->error == \Omi\User::LOGIN_DISABLED))
									$err_msg = "Invalid credentials combination!";
								else
									$err_msg = "There was an error while trying to login! Please try again later!";
							}
						?>

						@if ($err_msg)
							<div class="alert"><?= $err_msg	?></div>
						@endif

						<div>
							<label for="email" class="block text-sm font-medium leading-5 text-gray-700"><?= _T('5a2a662ed4f6a', 'Email') ?></label>
							<div class="mt-1 relative rounded-md shadow-sm">
								<input type="text" id="email" name="user" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" placeholder="name@email.com" value="<?= htmlentities($_POST["user"]) ?: "" ?>" />
							</div>
						</div>

						<div class="mt-6">
							<label for="email" class="block text-sm font-medium leading-5 text-gray-700"><?= _T('5a2a633646cd1', 'Password') ?></label>
							<div class="mt-1 relative rounded-md shadow-sm">
								<input type="password" id="password" name="pass" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" placeholder="*********" value="<?= htmlentities($_POST["pass"]) ?: "" ?>" />
							</div>
						</div>

						<div class="mt-6 flex items-center justify-between">
							<div class="flex items-center">
								<input id="remember_me" name="remember" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" />
								<label for="remember_me" class="ml-2 block text-sm leading-5 text-gray-900">{{_T(319, 'Remember me')}}</label>
							</div>

							<div class="text-sm leading-5">
								<a href="recover-password" class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition ease-in-out duration-150">{{_T(320,'Forgot your password')}}?</a>
							</div>
						</div>

						<div class="mt-6">
							<span class="block w-full rounded-md shadow-sm">
								<button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out">{{_T(321, 'Sign in')}}</button>
							</span>
						</div>

						<div class="mt-6">
							<span class="block w-full rounded-md shadow-sm">
								<a href="create-account" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gray-500 hover:bg-blue-500 focus:outline-none focus:border-blue-700 focus:shadow-outline-blue active:bg-blue-700 transition duration-150 ease-in-out">{{_T(188, 'Create account')}}</a>
							</span>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
