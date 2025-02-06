<div q-args="$data = null, $misc = null, $bind_params = null, $grid_mode = null, $id = null, $vars_path = '', $_qengine_args = null" class="min-h-screen bg-main-accent-color flex items-center justify-center p-6">
	@php $user = \Omi\User::GetCurrentUser();
	
	<div class="w-full relative">
		<div class="qc-grid-properties" data-properties="{{$this->getJsProperties()}}"></div>
		<div class="sm:mx-auto sm:w-full sm:max-w-md">
			<img src="<?= Q_APP_REL . 'code/res/main/images/logo.png' ?>" class="h-16 mx-auto" />

			<h2 class="text-center text-xl leading-9 font-extrabold text-white">
			  {{_T(188, 'Create account')}}
			</h2>
			<!-- <p class="mt-2 text-center text-sm leading-5 text-gray-600 max-w">
				Or
				<a href="#" class="font-medium text-blue-600 hover:text-blue-500 focus:outline-none focus:underline transition ease-in-out duration-150">
				  start your 14-day free trial
				</a>
			</p> -->
		</div>
		
		<div class="mt-6 sm:mx-auto sm:w-full sm:max-w-2xl">
			<div class="bg-white py-6 px-4 shadow sm:rounded-lg sm:px-10">
				<form class="xg-form" enctype='multipart/form-data' method='POST' autocomplete='off'>
					<input type="hidden" name="__submitted" value="1" />
					
					@if ($user && $user->Id && $user->Owner->Id)
						<input type="hidden" name="User[Id]" value="{{$user->Id}}" />
						<input type="hidden" name="Company[Id]" value="{{$user->Owner->Id}}" />
					@endif

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
						
						@if ((!defined('Q_HIDE_USERNAME_IN_PASS_RECOVERY')) || !Q_HIDE_USERNAME_IN_PASS_RECOVERY)
							<div>
								<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('5a2a66622a128', 'Username') ?></label>
								<div class="mt-1 relative rounded-md shadow-sm">
									<input type="text" name="username" value="{{$user->Username ?: ''}}" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
								</div>
							</div>
						@endif

						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
							<div class="js-form-grp">
								<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('190', 'Company name') ?> <span class="text-red-500 required">*</span></label>
								<input type="text" name="Company[Name]" value="{{($user && $user->Owner) ? $user->Owner->Name : ''}}" {{$user ? 'readonly' : ''}} class="readonly:border-0 readonly:px-0 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element qc-input q-mandatory" q-valid='((!empty($value) || ($value === "0"))) && ((!empty($value)) || ($value === "0"))' />
								<div class="text-xs mt-1">
									<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b><?= _T('190', 'Company name') ?></b> <?= _T('299', 'is mandatory_password') ?></span>					
								</div>
							</div>
							<div class="js-form-grp">
								<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('62', 'VAT No') ?> <span class="text-red-500 required">*</span></label>
								<input type="text" name="Company[VAT_No]" value="{{($user && $user->Owner) ? $user->Owner->VAT_No : ''}}" {{$user ? 'readonly' : ''}} class="readonly:border-0 readonly:px-0 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element qc-input q-mandatory" q-valid='((!empty($value) || ($value === "0"))) && ((!empty($value)) || ($value === "0"))' />
								<div class="text-xs mt-1">
									<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b><?= _T('62', 'VAT No') ?></b> <?= _T('196', 'is mandatory') ?></span>					
								</div>
							</div>
							<div class="js-form-grp">
								<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('61', 'Reg No') ?> <span class="text-red-500 required">*</span></label>
								<input type="text" name="Company[Reg_No]" value="{{($user && $user->Owner) ? $user->Owner->Reg_No : ''}}" {{$user ? 'readonly' : ''}} class="readonly:border-0 readonly:px-0 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element qc-input q-mandatory" q-valid='((!empty($value) || ($value === "0"))) && ((!empty($value)) || ($value === "0"))' />
								<div class="text-xs mt-1">
									<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b><?= _T('61', 'Reg No') ?></b> <?= _T('196', 'is mandatory') ?></span>					
								</div>
							</div>
							
							<div class="js-details-box_Address">
								<div xg-property='Address' class="qc-xg-property js-container-Address qc-prop-Address form-input-focus  ">
									<label class="block text-sm font-medium leading-5 text-gray-700 label-for-Address qc-xg-property-label">
										{{_L('Address')}} <span class="text-red-500 required">*</span>
									</label>
									<div class="relative mb-3 prop-wrapper-Address">
										<div class="js-form-grp">
											<div
												 class='flex qc-dd-wr relative items-center qc-ref-ctrl  qc-with-ctrl-dd-deprecated  qc-dd-insert-full-data'
												 q-path='Company[Address]'>
												<div class="flex-1 mr-3">
													@if (isset($user->Owner->Address))
														{{$user->Owner->Address->getModelCaption()}}
													@else
														@include (\Omi\View\DropDown, "Addresses", "Street,StreetNumber,Building,BuildingPart,Organization,Caption,Premise,PostCode,Details,City.{Name},County.{Name},Country.{Name},Latitude,Longitude", ['noItemCaption' => 'Select', "@CALL" => "Omi\\Util\\AddressSearch::Search_Address_For_DropDown"], ((($this->data->Company->Address !== null) ? $this->data->Company->Address : '')) ? (($this->data->Company->Address !== null) ? $this->data->Company->Address : '')->getModelCaption() : "Select", ((($this->data->Company->Address !== null) ? $this->data->Company->Address : '')) ? (($this->data->Company->Address !== null) ? $this->data->Company->Address : '')->toJSON() : null, 'Company[Address]'."[Id]", ((($this->data->Company->Address !== null) ? $this->data->Company->Address : '')) ? (($this->data->Company->Address !== null) ? $this->data->Company->Address : '')->getId() : null, 'Company[Address]'."[_ty]", ((($this->data->Company->Address !== null) ? $this->data->Company->Address : '')) ? get_class((($this->data->Company->Address !== null) ? $this->data->Company->Address : '')) : null, "name-x", "qc-form-element", null)
													@endif
												</div>
											</div>
											<div class="text-xs mt-1">
												<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b>{{_L('City')}}</b> {{_L('is mandatory')}}</span>
											</div>
										</div>
									</div>
								</div>
							</div>
														
							@if ($user && $user->Owner)
								<div class="">
									<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('212', 'Type') ?></label>
									<input type="hidden" value="{{$user->Owner->Is_Property_Owner ? 'property_owner' : ($user->Owner->Is_Channel_Owner ? 'channel_owner' : '')}}" name="Company[Is_Property_Owner]" />
									<input type="hidden" value="{{$user->Owner->Is_Property_Owner ? 'property_owner' : ($user->Owner->Is_Channel_Owner ? 'channel_owner' : '')}}" name="Company[Owner_Type]" />
									<div class="">
										@if ($user->Owner->Is_Property_Owner)
											{{_T(191, 'Property Owner')}}
										@else if ($user->Owner->Is_Channel_Owner)
											{{_T(192, 'Channel Owner')}}
										@endif
									</div>
								</div>
								<div>&nbsp;</div>
							@else
								<div class="flex items-center">
									<input id="is_property_owner" value="property_owner" name="Company[Owner_Type]" {{$user->Owner->Is_Property_Owner ? 'checked' : ''}} type="radio" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out js-property-owner" />
									<label for="is_property_owner" class="ml-2 block text-sm leading-5 text-gray-900">{{_T(191, 'Property Owner')}}</label>
								</div>
								<div class="flex items-center">
									<input id="is_channel_owner" value="channel_owner" name="Company[Owner_Type]" {{$user->Owner->Is_Channel_Owner ? 'checked' : ''}} type="radio" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out js-channel-owner" />
									<label for="is_channel_owner" class="ml-2 block text-sm leading-5 text-gray-900">{{_T(192, 'Channel Owner')}}</label>
								</div>
							@endif
						</div>
						
						@if ($user && $user->Owner)
							<label class="block text-sm font-medium leading-5 text-gray-700">Channel Manager <span class="text-red-500 required">*</span></label>
							<p class="mb-8">{{$user->TFH_API_System->Name}}</p>
						@else
							<div class="js-form-grp mb-8 js-channel-manager">
								<label class="block text-sm font-medium leading-5 text-gray-700">Channel Manager <span class="text-red-500 required">*</span></label>
									<div q-path='User[TFH_API_System]' class='flex qc-dd-wr relative items-center qc-ref-ctrl  '>
										<div class="w-full">
											<div class="qc-dd omi-control relative js-dd q-hide-on-click-away  QWebControl" 
											jsFunc="render($from, $selector, $binds, $caption, $full_data, $input_id_name, $input_id_default, $input_type_name, 
											   $input_type_default, $input_name_name, $inputs_extra_class, $input_data_name, $input_data_default, $picker_prop)" 
											q-args="$from = null, $selector = null, $binds = null, $caption = null, $full_data = null, $input_id_name = null, $input_id_default = null, 
											   $input_type_name = null, $input_type_default = null, $input_name_name = 'name', $inputs_extra_class = null, $attrs = null, $picker_name = null, $cssClass = null, $picker_placeholder = null"
											qCtrl="(Omi\View\DropDown)" q-valid='((!empty($value) || ($value === "0")))' q-dyn-parent="Omi\App\View\Users" q-dyn-inst="">

										   <input class="qc-dd-from" type="hidden" value="API_Systems" />
										   <input class="qc-dd-selector" type="hidden" value="Name" />
										   <input type="hidden" class="qc-dd-input-id qc-form-element q-mandatory" name-x="User[TFH_API_System][Id]" value="" />
										   <input type="hidden" class="qc-dd-input-ty qc-form-element q-mandatory" name-x="User[TFH_API_System][_ty]" value="" />

										   <input class="qc-dd-full-data" type="hidden" value="" />

										   <div class="qc-dd-pick cursor-pointer form-select block w-full pl-3 pr-10 py-2 text-base leading-6 border-gray-300 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 sm:text-sm sm:leading-5">Select</div>	
										   <div class="qc-dd-box q-hide-on-click-away-container rounded-md bg-white shadow-xs hidden origin-top-right absolute right-0 w-full rounded-md shadow-lg z-10">
											   <div class="qc-dd-search">
												   <input type="text" value="" placeholder="Search for API_Systems here" class="mt-1 form-input block w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none transition duration-150 ease-in-out sm:text-sm sm:leading-5" />
											   </div>
											   <div class="qc-dd-items">

											   </div>
										   </div>
										</div>
									</div>			
								</div>
								<div class="text-xs mt-1">
									<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b>Channel manager</b> este obligatoriu</span>					
								</div>
							</div>
						@endif
						
						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">							
							<div class="js-form-grp">
								<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('193', 'User Firstname') ?> <span class="text-red-500 required">*</span></label>
								<input type="text" name="User[Person][Firstname]" value="{{($user && $user->Person) ? $user->Person->Firstname : ''}}" {{$user->Person->Firstname ? 'readonly' : ''}} class="readonly:border-0 readonly:px-0 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element qc-input q-mandatory" q-valid='((!empty($value) || ($value === "0"))) && ((!empty($value)) || ($value === "0"))' />
								<div class="text-xs mt-1">
									<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b><?= _T('193', 'User Firstname') ?></b> <?= _T('196', 'is mandatory') ?></span>					
								</div>
							</div>
							<div class="js-form-grp">
								<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('194', 'User Name') ?> <span class="text-red-500 required">*</span></label>
								<input type="text" name="User[Person][Name]" value="{{($user && $user->Person) ? $user->Person->Name : ''}}" {{$user->Person->Name ? 'readonly' : ''}} class="readonly:border-0 readonly:px-0 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element qc-input q-mandatory" q-valid='((!empty($value) || ($value === "0"))) && ((!empty($value)) || ($value === "0"))' />
								<div class="text-xs mt-1">
									<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b><?= _T('194', 'User Name') ?></b> <?= _T('196', 'is mandatory') ?></span>					
								</div>
							</div>
							<div class="js-form-grp">
								<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('56', 'Email') ?> <span class="text-red-500 required">*</span></label>
								<input type="text" name="User[Person][Email]" value="{{($user && $user->Person) ? $user->Person->Email : ''}}" {{$user->Person->Email ? 'readonly' : ''}} class="readonly:border-0 readonly:px-0 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element qc-input q-mandatory" q-valid='(($value === null) || ($value === "") || (filter_var($value, FILTER_VALIDATE_EMAIL))) && ((!empty($value)) || ($value === "0"))' />
								<div class="text-xs mt-1">
									<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b><?= _T('298', 'Email_create_account') ?></b> <?= _T('196', 'is mandatory') ?></span>					
								</div>
							</div>
							<div class="js-form-grp">
								<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('55', 'Phone') ?> <span class="text-red-500 required">*</span></label>
								<input type="text" name="User[Person][Phone]" value="{{($user && $user->Person) ? $user->Person->Phone : ''}}" {{$user->Person->Phone ? 'readonly' : ''}} class="readonly:border-0 readonly:px-0 appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element qc-input q-mandatory" q-valid='((!empty($value)) && (strlen($value) === 10) && (strlen(preg_replace(/[^0-9]/g, "", $value)) === 10))' />
								<div class="text-xs mt-1">
									<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b><?= _T('336', 'Phone_create_account') ?></b> <?= _T('196', 'is mandatory') ?></span>					
								</div>
							</div>
							
							@if (!$user)
								<div class="js-form-grp">
									<label class="block text-sm font-medium leading-5 text-gray-700"><?= _T('195', 'Password') ?> <span class="text-red-500 required">*</span></label>
									<input type="password" name="User[Password]" value="" class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 transition duration-150 ease-in-out sm:text-sm sm:leading-5 qc-form-element qc-input q-mandatory" q-valid='((!empty($value) || ($value === "0"))) && ((!empty($value)) || ($value === "0"))' />
									<div class="text-xs mt-1">
										<span class="qc-validation-alert text-red-600 hidden" data-tag="mandatory"><b><?= _T('195', 'Password') ?></b> <?= _T('299', 'is mandatory_password') ?></span>					
										<span class="text-gray-600"><?= _T('201', '8 characters minimum, at least a number a caps letter and one special symbol') ?></span>					
									</div>
								</div>
							@endif
						</div>
						
						<div class="flex items-center mb-6">
							<input id="accept_terms" data-errmsg="{{_T(247, 'You must accept terms')}}" name="Terms" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" />
							<label for="accept_terms" class="ml-2 block text-sm leading-5 text-gray-900">
								{{_T(198, 'I agree with')}}
								<a href="javascript: void(0);" class="js-terms-popup text-blue-600 underline">{{_T(199, 'terms and conditions')}}</a>
							</label>
						</div>
						
						<div class="flex items-center mb-6">
							<input id="privacy_policy" name="Terms_Privacy" type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 transition duration-150 ease-in-out" />
							<label for="privacy_policy" class="ml-2 block text-sm leading-5 text-gray-900">
								{{_T(198, 'I agree with')}}
								<a href="javascript: void(0);" class="js-policy-popup text-blue-600 underline">{{_T(202, 'privacy policy')}}</a>
							</label>
						</div>
						
						<div class="g-recaptcha w-full mb-6"
							<?= (defined('RECAPTCHA_INVISIBLE') && RECAPTCHA_INVISIBLE) ? ' data-badge="bottomleft" data-size="invisible"' : '' ?> 
							data-expired-callback="contact_RecaptchaExpiredCallback"
							data-callback="contact_RecaptchaCallback"
							data-sitekey="<?= RECAPTCHA_SITE_KEY ?>">
						</div>

						<a href="terms-and-conditions" class="qc-submit-btn flex justify-center mb-4 items-center px-4 py-2 border text-sm leading-5 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-500 focus:outline-none focus:shadow-outline-blue focus:border-blue-300 active:text-white active:bg-indigo-500 active:text-white transition duration-150 ease-in-out">
							@if ($user)
								<?= _T('5a2fa73ca20f6', 'Save') ?>
							@else
								<?= _T('189', 'Create') ?>
							@endif
						</a>
						@if (!$user)
							<a class="mt-6 w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md border border-gray-300 text-blue-600 bg-white hover:bg-white focus:outline-none focus:border-white focus:shadow-outline-white active:bg-white transition duration-150 ease-in-out" href="<?= qUrl("login") ?>"><?= _T('5a2a664c55a5e', 'Back to Login') ?></a>
						@endif
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
