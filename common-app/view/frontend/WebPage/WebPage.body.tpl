<body q-args="$userData = null, $branding = null" class="antialiased jx-controls">
    <?php
        // to be replaced or to have equivalent in javascript
		$user = \QApi::Call('\Omi\User::GetCurrentUser');
		$property_filter = \Omi\View\Controller::TFH_Get_PropertyFilter();
        $controller = \QApp::$UrlController;
        $owner = $userData ? \QApi::Call('GetCurrentOwner') : null;
        $logo = ($branding && ($lfp = $branding->getFullPath("Logo")) && file_exists($lfp) && is_file($lfp)) ? $lfp : null;
        
        if (!$logo && Default_Logo && file_exists(Default_Logo))
            $logo = Default_Logo;
		
		// is agency
		$is_H2B_Channel = false;
		$isSuperadmin = \Omi\User::Is_Superadmin();
		$isPropertyOwner = false;
		$is_H2B_Superadmin = $isSuperadmin;
		
		$url = \QWebRequest::GetOriginalRequest();
		$urlObj = new \QUrl($url);
		$originalRequest = $urlObj->reset();
		
		if (($originalRequest == 'create-account') || ($originalRequest == 'terms-and-conditions') || ($originalRequest == 'privacy-policy'))
			$hideSidebar = true;
    ?>
	
	@if ($userData)
		<div id="preloader" style="display: none;">
			<div class="inner">
				<span class="loader"></span>
			</div>
		</div>
	@endif
    
    <div class="h-screen flex {{(!$userData) ? 'bg-main-accent-color' : 'bg-cool-gray-100'}} <?= ($userData && !$hideSidebar) ? 'lg:ml-64 ml-0' : ''; ?> overflow-hidden js-page-content">
        @if ($userData && !$hideSidebar)
			@include($this::nav)
        @endif
    
        <div class="flex-1 focus:outline-none overflow-auto" id="middle-content" tabindex="0">
            @if ($userData && !$hideSidebar)
				<div class="relative z-10 flex-shrink-0 flex h-16 bg-white border-b border-gray-200 items-center">
					<div class="lg:hidden flex">
						<a href="javascript: void(0);" class="p-4 py-1 ml-2 js-mobile-control-sidemenu">
							<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" class="h-6 w-6 group-focus:text-indigo-200 transition ease-in-out duration-150">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
							</svg>
						</a>
					</div>
					
					<div class="flex-1 px-4 flex justify-between sm:px-6 lg:px-8">
						<!-- <div class="flex-1 flex">
							<form class="w-full flex md:ml-0" action="#" method="GET">
								<label for="search_field" class="sr-only">Search</label>
								<div class="relative w-full text-cool-gray-400 focus-within:text-cool-gray-600">
									<div class="absolute inset-y-0 left-0 flex items-center pointer-events-none">
										<svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
											<path fill-rule="evenodd" clip-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"></path>
										</svg>
									</div>
									<input id="search_field" class="block w-full h-full pl-8 pr-3 py-2 rounded-md text-cool-gray-900 placeholder-cool-gray-500 focus:outline-none focus:placeholder-cool-gray-400 sm:text-sm" placeholder="Search" type="search" />
								</div>
							</form>
						</div> -->
					</div>

					<div class="ml-4 flex items-center md:ml-6">						
						@if ($isPropertyOwner || $is_H2B_Superadmin)
							<div id="tfh_webpage_property_select" class="max-w-64 w-full">
								@php $binds = ["Owner" => ($_town = \Omi\App::GetCurrentOwner()) ? $_town->getId() : 0, "Gby_Id" => true, 'OBY_Name' => "ASC"];
								@php $tfh_ppty = \Omi\View\Controller::TFH_Get_PropertyFilter();

								@include(\Omi\View\DropDown, "Properties", "Code,Name", $binds, $tfh_ppty ? $tfh_ppty->getModelCaption() : _T(364, 'Select property'), null, null, $tfh_ppty ? $tfh_ppty->Id : null)
							</div>
						@endif

						<!-- <button class="ml-3 p-1 text-cool-gray-400 rounded-full hover:bg-cool-gray-100 hover:text-cool-gray-500 focus:outline-none focus:shadow-outline focus:text-cool-gray-500" aria-label="Notifications">
						  <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
						  </svg>
						</button> -->

						<!-- MY ACCOUNT DROPDOWN :: BEGIN -->
						<div class="ml-3 relative mr-3" x-data="{ open: false }" @keydown.window.escape="open = false" @click.away="open = false">
							<div class="flex items-center">
								@php $owner = \Omi\User::GetCurrentUser()->Owner;
								@php $owner->populate('Name, Reg_No, VAT_No, Address.*, Contacts.*, Contact_Emails_List.*, Contact_Phones_List.*, Bank_Accounts.{Bank_Name, IBAN, Currency}');
								
								@if (!$owner->Name || !$owner->Reg_No || !$owner->VAT_No || !$owner->Address || !count($owner->Contacts) || !count($owner->Contact_Emails_List) || !count($owner->Contact_Phones_List) || !count($owner->Bank_Accounts))
									<div class="relative w-4 h-4 block" data-tippy-content="Compania dvs nu are toate datele completate">
										<span class="ringring"></span>
										<span class="w-3 h-3 bg-red-500 rounded-full block absolute top-0 left-0 z-10"></span>
									</div>
								@endif
								
								<button class="max-w-xs flex items-center text-sm rounded-full focus:outline-none focus:bg-cool-gray-100 lg:p-2 lg:rounded-md lg:hover:bg-cool-gray-100 js-userAccountDDTrigger" id="user-menu" aria-label="User menu" aria-haspopup="true" x-bind:aria-expanded="open" aria-expanded="true">
									<p class="whitespace-nowrap ml-3 text-cool-gray-700 text-sm leading-5 font-medium">{{substr($is_H2B_Channel ? \Omi\User::GetCurrentUser()->Owner->Name : ($property_filter ? $property_filter->Owner->Name : \Omi\User::GetCurrentUser()->Owner->Name), 0, 30)}}</p>
									<svg class="flex-shrink-0 ml-1 h-6 w-6 text-cool-gray-400" viewBox="0 0 20 20" fill="currentColor">
										<path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
									</svg>
								</button>
							</div>

							<div x-description="Profile dropdown panel, show/hide based on dropdown state." x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-64 rounded-md shadow-lg js-userAccountDD hidden">
								<div class="rounded-md bg-white shadow-xs" role="menu" aria-orientation="vertical" aria-labelledby="user-menu">
									<div class="flex px-4 py-2 bg-cool-gray-100 items-center">
										<svg class="hidden flex-shrink-0 h-5 w-5 text-cool-gray-400 lg:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
										</svg>
										<span class="block px-2 py-2 text-sm text-cool-gray-700 transition ease-in-out duration-150">
											{{($user->Person && ($user->Person->Firstname || $user->Person->Name)) ? ($user->Person->Firstname . ' ' . $user->Person->Name) : $user->Name}}

											@if (\Omi\User::GetCurrentUser()->Type === 'H2B_Superadmin')
												<br/><span style="color: red;">H2B Superadmin</span>
											@endif
										</span>
									</div>

									<a href="MyAccount" class="block px-4 py-2 text-sm text-cool-gray-700 hover:bg-cool-gray-100 transition ease-in-out duration-150" role="menuitem">{{_T(31, 'My Profile')}}</a>

									<!-- <a href="javascript: void(0);" class="block px-4 py-2 text-sm text-cool-gray-700 hover:bg-cool-gray-100 transition ease-in-out duration-150" role="menuitem">Settings</a> -->

									@if ($isPropertyOwner)
										<a href="Property_Owners" class="block px-4 py-2 text-sm text-cool-gray-700 hover:bg-cool-gray-100 transition ease-in-out duration-150" role="menuitem">{{_T(30, 'My Company')}}</a>
									@else if (\Omi\User::GetCurrentUser()->Type === 'H2B_Channel')
										<a href="Channels" class="block px-4 py-2 text-sm text-cool-gray-700 hover:bg-cool-gray-100 transition ease-in-out duration-150" role="menuitem">{{_T(30, 'My Company')}}</a>
									@endif

									<a href="<?= $controller->getUrlForTag('logout') ?>" class="block px-4 py-2 text-sm text-cool-gray-700 hover:bg-cool-gray-100 transition ease-in-out duration-150" role="menuitem">Logout</a>
								</div>
							</div>
						</div>
						<!-- MY ACCOUNT DROPDOWN :: BEGIN -->
					</div>
				</div>
            @endif

            @include($this::content, $userData);
			
			@if (false && $userData && (($originalRequest != 'terms-and-conditions') && ($originalRequest !== 'privacy-policy')))
				<footer id="footer" class="shadow">
					<a href="terms-and-conditions" target="_blank" class="text-blue-600 text-sm underline">{{_T(214, 'Terms and conditions')}}</a> | 
					<a href="privacy-policy" target="_blank" class="text-blue-600 text-sm underline">{{_T(324, 'Privacy policy')}}</a>
				</footer>
			@endif
        </div>
    </div>
    
    @include($this::bodyResources);
    
    <?php $this->renderCallbacks(); ?>
	<script type="text/javascript"> window._q_maximum_upload_size_ = <?= \Omi\View\Grid::maximum_upload_size(); ?>; </script>
</body>