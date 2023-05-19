<div class="min-h-screen bg-gray-50 flex items-center justify-center p-6" q-namespace="Omi\View" q-args="$user = null">
	<div class="w-full relative">
		<div class="sm:mx-auto sm:w-full sm:max-w-md absolute top-0 left-0 right-0 -mt-24">
			<img src="<?= Q_APP_REL . 'code/res/main/images/logo_blue.png' ?>" class="h-16 mx-auto" />

			<h2 class="text-center text-xl leading-9 font-extrabold text-gray-900">
				{{_T(339, 'Change Password')}}
			</h2>
			<!-- <p class="mt-2 text-center text-sm leading-5 text-gray-600 max-w">
				Or
				<a href="#" class="font-medium text-indigo-600 hover:text-indigo-500 focus:outline-none focus:underline transition ease-in-out duration-150">
				  start your 14-day free trial
				</a>
			</p> -->
		</div>
		
		<div class="mt-6 sm:mx-auto sm:w-full sm:max-w-md">
			<div class="bg-white py-6 px-4 shadow sm:rounded-lg sm:px-10">
				<form method="POST">
					@php $requested = \QUrl::$Requested."";
					@php $err = ($_SESSION["__err__"] && $_SESSION["__err__"][$requested]) ? $_SESSION["__err__"][$requested] : null;
					
					@if ($err)
						<div class="row m-bottom-20 if-not-60-fill-all bg-white card-padding card-shadow">
							<div class="text-red-500 text-sm font-medium" style=>
								<?= $err ?>
							</div>
						</div>
						
						@php unset($_SESSION["__err__"][$requested]);
					@endif

					<div class="login-wrapper">						
						<input type="hidden" name="Id" value="<?= $user ? $user->getId() : '' ?>" />
						<input type="hidden" name="PasswordRecoveryCode" value="<?= $user ? $user->PasswordRecoveryCode : '' ?>" />
						<input type="hidden" name="__submitted" value="1" />
						
						<div class="mt-6">
							<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('340', 'New Password') ?></label>
							<input type="password" id="newpass" name="_NewPassword" value="" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
						</div>
						
						<div class="mt-6">
							<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('341', 'Confirm New Password') ?></label>
							<input type="password" id="newpassconfirm" name="_NewPasswordConfirm" value="" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
						</div>
						
						<button onclick="save.apply(this); return false;" class="mt-6 w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:border-indigo-700 focus:shadow-outline-indigo active:bg-indigo-700 transition duration-150 ease-in-out"><?= _T('5a2a665480556', 'Save') ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>